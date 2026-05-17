<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending'           => ExpenseRequest::pending()->count(),
            'approved_today'    => ExpenseRequest::approved()
                                    ->whereDate('updated_at', today())->count(),
            'approved_total'    => ExpenseRequest::approved()->count(),
            'rejected'          => ExpenseRequest::rejected()->count(),
            'total_processed'   => ExpenseRequest::whereIn('status', ['approved', 'rejected'])->count(),
            'paid_total'        => ExpenseRequest::whereIn('status', ['paid', 'reimbursed', 'completed'])->count(),
            'monthly_amount'    => ExpenseRequest::approved()
                                    ->whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->sum('amount'),
            'total_employees'   => User::where('role', 'employee')->count(),
        ];

        // Pending queue — high/urgent first, then newest
        $pendingRequests = ExpenseRequest::with(['category', 'requester'])
            ->pending()
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->latest()
            ->limit(8)
            ->get();

        // Recent decisions
        $recentDecisions = ExpenseRequest::with(['requester'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')
            ->limit(6)
            ->get();

        // Attention items: high-value pending + long-waiting
        $attentionItems = collect();

        ExpenseRequest::with('requester')->pending()
            ->where('amount', '>=', 2000)
            ->orderByDesc('amount')
            ->limit(3)
            ->get()
            ->each(function ($r) use (&$attentionItems) {
                $attentionItems->push([
                    'tone'  => 'danger',
                    'title' => '₹' . number_format($r->amount, 0) . ' — ' . \Str::limit($r->title, 28),
                    'body'  => 'High-value · ' . ($r->requester?->name ?? '—'),
                    'url'   => route('manager.expense-requests.show', $r),
                ]);
            });

        ExpenseRequest::with('requester')->pending()
            ->where('created_at', '<=', now()->subHours(48))
            ->whereNotIn('id', $attentionItems->pluck('id')->filter())
            ->orderBy('created_at')
            ->limit(4)
            ->get()
            ->each(function ($r) use (&$attentionItems) {
                $attentionItems->push([
                    'tone'  => 'warning',
                    'title' => \Str::limit($r->title, 30),
                    'body'  => 'Waiting ' . $r->created_at->diffForHumans() . ' · ' . ($r->requester?->name ?? '—'),
                    'url'   => route('manager.expense-requests.show', $r),
                ]);
            });

        return view('manager.dashboard', compact(
            'stats',
            'pendingRequests',
            'recentDecisions',
            'attentionItems',
        ));
    }
}
