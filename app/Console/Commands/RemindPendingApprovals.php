<?php

namespace App\Console\Commands;

use App\Models\ExpenseRequest;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class RemindPendingApprovals extends Command
{
    protected $signature   = 'app:remind-pending';
    protected $description = 'Send reminders for expense requests pending approval for over 24 hours';

    public function handle(NotificationService $notificationService): int
    {
        $oldPending = ExpenseRequest::pending()
            ->where('created_at', '<=', now()->subHours(24))
            ->with('requester')
            ->get();

        if ($oldPending->isEmpty()) {
            $this->info('No overdue pending requests.');
            return self::SUCCESS;
        }

        $count = $oldPending->count();
        $notificationService->sendToManagers(
            'pending_reminder',
            "{$count} expense request(s) awaiting approval",
            "There are {$count} requests pending approval for over 24 hours.",
            route('admin.expense-requests.index', ['status' => 'pending'])
        );

        $this->info("Reminder sent for {$count} pending requests.");
        return self::SUCCESS;
    }
}
