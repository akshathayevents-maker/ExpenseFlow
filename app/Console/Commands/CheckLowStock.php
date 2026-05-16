<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Services\InventoryService;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature   = 'app:check-stock';
    protected $description = 'Check inventory for low/out-of-stock items and create alerts';

    public function handle(InventoryService $inventoryService): int
    {
        $criticalItems = InventoryItem::active()->critical()->get();

        $this->info("Checking {$criticalItems->count()} critical items...");

        $systemUser = \App\Models\User::where('role', 'admin')->first();
        if (! $systemUser) {
            $this->error('No admin user found.');
            return self::FAILURE;
        }

        foreach ($criticalItems as $item) {
            $inventoryService->checkAndCreateAlerts($item, $systemUser);
            $this->line("  [{$item->name}] Stock: {$item->current_stock} {$item->unit} (min: {$item->minimum_stock})");
        }

        $this->info('Stock check complete.');
        return self::SUCCESS;
    }
}
