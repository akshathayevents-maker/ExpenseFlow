<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $filters = [
            'module'  => $request->input('module', ''),
            'action'  => $request->input('action', ''),
            'user_id' => $request->input('user_id', ''),
            'from'    => $request->input('from', ''),
            'to'      => $request->input('to', ''),
        ];

        $filtered = fn () => AuditLog::query()
            ->when($filters['module']  ?: null, fn ($q, $v) => $q->where('module', $v))
            ->when($filters['action']  ?: null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['user_id'] ?: null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['from']    ?: null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to']      ?: null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        // CSV export — returns early before building the page
        if ($request->boolean('export')) {
            return $this->streamCsv($filtered);
        }

        $logs = $filtered()
            ->with('user')
            ->latest()
            ->paginate(40)
            ->withQueryString();

        // Group current page by calendar date for the timeline view
        $grouped = $logs->getCollection()
            ->groupBy(fn ($log) => $log->created_at->toDateString());

        // Operational insight stats (from filtered set)
        $insights = [
            'today'     => AuditLog::whereDate('created_at', today())->count(),
            'total'     => $filtered()->count(),
            'approvals' => $filtered()->whereIn('action', ['approved', 'verified', 'finalized'])->count(),
            'financial' => $filtered()->whereIn('action', ['credited', 'debited', 'settled', 'reimbursed', 'paid'])->count(),
            'critical'  => $filtered()->whereIn('action', ['deleted', 'rejected', 'cancelled'])->count(),
        ];

        $topRow = $filtered()
            ->select('user_id', DB::raw('count(*) as cnt'))
            ->groupBy('user_id')
            ->orderByDesc('cnt')
            ->first();
        $insights['top_user'] = $topRow ? User::find($topRow->user_id)?->name : null;

        $modules = AuditLog::distinct()->orderBy('module')->pluck('module');
        $actions = AuditLog::distinct()->orderBy('action')->pluck('action');
        $users   = User::orderBy('name')->get();

        return view('admin.audit-logs.index',
            compact('logs', 'grouped', 'filters', 'modules', 'actions', 'users', 'insights'));
    }

    private function streamCsv(callable $filtered): StreamedResponse
    {
        $logs = $filtered()->with('user')->latest()->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Time', 'User', 'Action', 'Module', 'Reference', 'IP Address']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at->format('d M Y'),
                    $log->created_at->format('h:i A'),
                    $log->user?->name ?? '—',
                    ucfirst($log->action),
                    ucfirst(str_replace('_', ' ', $log->module)),
                    $log->reference_label ?? ($log->reference_id ? "#{$log->reference_id}" : '—'),
                    $log->ip_address ?? '—',
                ]);
            }
            fclose($out);
        }, 'audit-logs-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
