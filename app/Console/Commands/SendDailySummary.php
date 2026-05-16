<?php

namespace App\Console\Commands;

use App\Models\ExpenseRequest;
use App\Models\InventoryStockAlert;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendDailySummary extends Command
{
    protected $signature   = 'app:daily-summary';
    protected $description = 'Send daily expense and operational summary to admins';

    public function handle(NotificationService $notificationService): int
    {
        $today = today();

        $expenseCount  = ExpenseRequest::whereDate('created_at', $today)->count();
        $expenseTotal  = ExpenseRequest::whereDate('created_at', $today)
            ->whereNotIn('status', ['pending', 'rejected'])
            ->sum('amount');
        $pendingCount  = ExpenseRequest::pending()->count();
        $lowStockCount = InventoryStockAlert::where('is_resolved', false)->count();

        $title = "Daily Summary — {$today->format('d M Y')}";
        $body  = "Expenses today: {$expenseCount} (₹{$expenseTotal}). Pending approvals: {$pendingCount}. Active stock alerts: {$lowStockCount}.";

        $notificationService->sendToAdmins(
            'daily_summary',
            $title,
            $body,
            route('admin.reports.daily')
        );

        $this->info("Daily summary sent. {$expenseCount} expenses, ₹{$expenseTotal} total.");
        return self::SUCCESS;
    }
}
