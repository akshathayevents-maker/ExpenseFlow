<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['module', 'action', 'user_id', 'from', 'to']);

        $logs = AuditLog::with('user')
            ->when($filters['module']  ?? null, fn ($q, $v) => $q->where('module', $v))
            ->when($filters['action']  ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['from']    ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to']      ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $modules = AuditLog::distinct()->pluck('module');
        $actions = AuditLog::distinct()->pluck('action');
        $users   = User::orderBy('name')->get();

        return view('admin.audit-logs.index', compact('logs', 'filters', 'modules', 'actions', 'users'));
    }
}
