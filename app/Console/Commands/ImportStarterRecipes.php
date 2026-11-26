<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Recipes\StarterRecipeImporter;
use Illuminate\Console\Command;

class ImportStarterRecipes extends Command
{
    protected $signature = 'recipes:import-starter {--owner= : email of the admin recorded as creator (default: first active admin)}';

    protected $description = 'Import the hand-curated starter recipes (not AI) as "not verified"; safe to re-run, existing dishes are skipped';

    public function handle(StarterRecipeImporter $importer): int
    {
        $owner = $this->option('owner')
            ? User::where('email', $this->option('owner'))->first()
            : User::where('role', 'admin')->where('is_active', true)->orderBy('id')->first();
        if (! $owner) {
            $this->error('No admin user found (use --owner=email).');

            return self::FAILURE;
        }
        $r = $importer->import($owner);
        $this->info(count($r['created']).' imported, '.count($r['skipped']).' already in the library, '.count($r['failed']).' failed.');
        $this->line('Tamil added to existing curated recipes: '.count($r['tamil_updated']).($r['tamil_updated'] ? ' ('.implode(', ', $r['tamil_updated']).')' : ''));
        $this->line('Flagged as possible duplicates: '.count($r['similar']).($r['similar'] ? ' ('.implode(', ', $r['similar']).')' : ''));
        if ($r['verified_needs_manual_tamil']) {
            $this->warn('Verified recipes needing Tamil steps added by hand (not changed): '.implode(', ', $r['verified_needs_manual_tamil']));
        }
        foreach ($r['failed'] as $name => $err) {
            $this->warn("  {$name}: {$err}");
        }
        $this->line('Imported recipes are NOT verified and are hidden from the kitchen until an admin verifies them (Kitchen → Recipes → Review queue).');

        return $r['failed'] ? self::FAILURE : self::SUCCESS;
    }
}
