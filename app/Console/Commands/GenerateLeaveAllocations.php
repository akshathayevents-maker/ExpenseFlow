<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LeaveAllocationService;
use Illuminate\Console\Command;

class GenerateLeaveAllocations extends Command
{
    protected $signature = 'leave:generate-allocations';

    protected $description = 'Generate any newly-eligible leave allocations (yearly/monthly/quarterly) for all employees. Safe to run daily — idempotent via the employee_leave_allocations unique constraint.';

    public function handle(LeaveAllocationService $service): int
    {
        $asOf = now()->startOfDay();
        $totalCreated = 0;

        User::whereIn('role', ['employee', 'manager'])
            ->whereHas('leavePolicies', fn ($q) => $q->where('is_active', true))
            ->chunkById(100, function ($users) use ($service, $asOf, &$totalCreated) {
                foreach ($users as $user) {
                    $created = $service->generateForUser($user, $asOf);
                    $totalCreated += count($created);
                }
            });

        $this->info("Leave allocation generation complete — {$totalCreated} new allocation(s) created.");

        return self::SUCCESS;
    }
}
