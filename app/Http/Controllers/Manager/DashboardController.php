<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending'          => ExpenseRequest::pending()->count(),
            'approved'         => ExpenseRequest::approved()->count(),
            'rejected'         => ExpenseRequest::rejected()->count(),
            'total_employees'  => User::where('role', 'employee')->count(),
            'monthly_expense'  => ExpenseRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'approved')
                ->sum('amount'),
        ];

        $pendingRequests = ExpenseRequest::with(['category', 'requester'])
            ->pending()
            ->latest()
            ->limit(5)
            ->get();

        return view('manager.dashboard', compact('stats', 'pendingRequests'));
    }
}
