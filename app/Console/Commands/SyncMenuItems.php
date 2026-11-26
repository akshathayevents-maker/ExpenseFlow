<?php

namespace App\Console\Commands;

use App\Services\MenuItemSync;
use Illuminate\Console\Command;

class SyncMenuItems extends Command
{
    protected $signature = 'menu:sync-items';

    protected $description = 'Make Menu Items (/menu/items) and the Event Request menu the same list of dishes (safe to re-run)';

    public function handle(MenuItemSync $sync): int
    {
        $r = $sync->syncAll();
        $this->info("Menu items added: {$r['menu_created']}; event-request dishes added (inactive, ₹0): {$r['er_created']}.");
        $this->line("Now {$r['total_menu']} menu items and {$r['total_er']} event-request dishes; {$r['linked']} linked.");

        return self::SUCCESS;
    }
}
