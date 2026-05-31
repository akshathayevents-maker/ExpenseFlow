<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan db:health-check           — check only, report missing tables/columns
 * php artisan db:health-check --fix     — run `php artisan migrate` then re-check
 * php artisan db:health-check --verbose — show OK items too, not just failures
 */
class DbHealthCheck extends Command
{
    protected $signature   = 'db:health-check {--fix : Run php artisan migrate if issues found} {--show-ok : Show passing checks too, not just failures}';
    protected $description = 'Check every table and column defined in migrations actually exists in the database';

    // ── Manifest: every table + columns we expect ───────────────────────────────
    // Generated from migrations. Update this list when adding new migrations.
    private const EXPECTED = [
        'users' => [
            'id','name','email','email_verified_at','password','remember_token',
            'created_at','updated_at',
            // add_role_phone_status
            'phone','role','is_active',
        ],
        'password_reset_tokens' => ['email','token','created_at'],
        'sessions'              => ['id','user_id','ip_address','user_agent','payload','last_activity'],
        'cache'                 => ['key','value','expiration'],
        'cache_locks'           => ['key','owner','expiration'],
        'jobs'                  => ['id','queue','payload','attempts','reserved_at','available_at','created_at'],
        'job_batches'           => ['id','name','total_jobs','pending_jobs','failed_jobs','failed_job_ids','options','cancelled_at','created_at','finished_at'],
        'failed_jobs'           => ['id','uuid','connection','queue','payload','exception','failed_at'],
        'expense_categories'    => ['id','name','description','is_active','created_at','updated_at'],
        'vendors'               => ['id','name','phone','address','notes','is_active','created_at','updated_at'],
        'expense_requests'      => [
            'id','title','expense_category_id','vendor_id','requested_by','approved_by',
            'amount','notes','priority','status','rejection_reason','approved_at',
            'created_at','updated_at',
            // update_for_settlement
            'settlement_type',
            // add_qr_payment_fields
            'qr_file_path','whatsapp_sent_at',
        ],
        'expense_bills'         => ['id','expense_request_id','file_path','original_name','mime_type','file_size','uploaded_by','created_at','updated_at'],
        'wallets'               => ['id','user_id','balance','created_at','updated_at'],
        'expense_payments'      => [
            'id','expense_request_id','payment_mode','amount','transaction_reference',
            'payment_notes','paid_by','paid_at','created_at','updated_at',
            // add_proof_path
            'proof_file_path',
        ],
        'wallet_transactions'   => ['id','wallet_id','expense_request_id','type','amount','balance_before','balance_after','notes','created_by','created_at','updated_at'],
        'inventory_categories'  => ['id','name','description','is_active','created_at','updated_at'],
        'inventory_items'       => ['id','name','sku','inventory_category_id','unit','current_stock','minimum_stock','maximum_stock','average_cost','description','status','created_at','updated_at'],
        'inventory_transactions'=> ['id','inventory_item_id','type','quantity','balance_before','balance_after','unit_cost','notes','created_by','reference_type','reference_id','created_at','updated_at'],
        'inventory_stock_alerts'=> ['id','inventory_item_id','alert_type','stock_at_alert','is_resolved','resolved_at','resolved_by','notes','created_at','updated_at'],
        'purchase_plans'        => ['id','title','planned_date','status','notes','created_by','approved_by','approved_at','created_at','updated_at'],
        'purchase_plan_items'   => ['id','purchase_plan_id','inventory_item_id','suggested_quantity','estimated_unit_cost','priority','notes','created_at','updated_at'],
        'app_notifications'     => ['id','user_id','type','title','body','link','data','read_at','created_at','updated_at'],
        'audit_logs'            => ['id','user_id','action','module','reference_id','reference_label','old_values','new_values','ip_address','user_agent','created_at','updated_at'],
        'daily_closings'        => [
            'id','date','status','expense_total','payment_total','stock_additions','stock_deductions',
            'expense_count','notes','created_by','verified_by','verified_at','created_at','updated_at',
            // add_updated_by
            'updated_by',
            // add_balance_fields
            'opening_balance','total_credit','total_debit','closing_balance','finalized_at','snapshot_captured',
        ],
        'settings'                       => ['id','key','value','type','group','label','created_at','updated_at'],
        'daily_closing_expenses'         => ['id','daily_closing_id','original_expense_id','employee_id','category_id','title','amount','payment_status','remarks','removed','created_at','updated_at'],
        'daily_closing_adjustments'      => ['id','daily_closing_id','type','amount','reason','notes','created_by','created_at','updated_at'],
        'daily_closing_audits'           => ['id','daily_closing_id','action_type','field_name','old_value','new_value','remarks','changed_by','created_at'],
        'inventory_bill_uploads'         => ['id','vendor_name','invoice_number','invoice_date','gst_number','subtotal','tax_amount','total_amount','original_filename','stored_path','file_type','file_hash','extracted_json','ocr_provider','status','notes','uploaded_by','reviewed_by','created_at','updated_at'],
        'inventory_bill_items'           => ['id','bill_upload_id','inventory_item_id','item_name','sku','quantity','unit','unit_price','tax_percent','total','category_id','raw_extracted_text','imported','created_at','updated_at'],
        'halls'                          => ['id','name','description','capacity','location','is_active','created_at','updated_at'],
        'meal_plans'                     => ['id','name','category','description','price_per_person','is_active','created_at','updated_at','deleted_at'],
        'hall_bookings'                  => [
            'id','hall_id','meal_plan_id','created_by','customer_name','customer_mobile','customer_alt_mobile',
            'event_type','booking_date','start_time','end_time','number_of_people',
            'has_breakfast','has_lunch','has_dinner',
            'total_amount','advance_amount','payment_status','status','notes','created_at','updated_at',
            // add_hall_cost
            'hall_cost',
        ],
        'hall_booking_meals'             => ['id','hall_booking_id','meal_type','guest_count','special_requirements','created_at','updated_at'],
        'booking_payments'               => ['id','hall_booking_id','recorded_by','amount','payment_method','reference_number','payment_type','paid_at','notes','created_at','updated_at'],
        'booking_additional_services'    => ['id','hall_booking_id','service_name','description','amount','created_at','updated_at'],
    ];

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>ExpenseFlow — Database Health Check</>');
        $this->line('  <fg=gray>Connection: ' . config('database.default') . ' / ' . config('database.connections.' . config('database.default') . '.database') . '</>');
        $this->newLine();

        [$missingTables, $missingColumns] = $this->runChecks();

        $totalIssues = count($missingTables) + count($missingColumns);

        if ($totalIssues === 0) {
            $this->line('  <fg=green;options=bold>✔  All ' . count(self::EXPECTED) . ' tables and their columns are present.</>');
            $this->newLine();
            return self::SUCCESS;
        }

        // ── Report issues ────────────────────────────────────────────────────────
        if ($missingTables) {
            $this->line('  <fg=red;options=bold>MISSING TABLES (' . count($missingTables) . ')</>');
            foreach ($missingTables as $tbl) {
                $this->line("  <fg=red>  ✘  {$tbl}</>");
            }
            $this->newLine();
        }

        if ($missingColumns) {
            $this->line('  <fg=yellow;options=bold>MISSING COLUMNS (' . count($missingColumns) . ')</>');
            foreach ($missingColumns as [$tbl, $col]) {
                $this->line("  <fg=yellow>  ✘  {$tbl}.{$col}</>");
            }
            $this->newLine();
        }

        // ── Auto-fix ─────────────────────────────────────────────────────────────
        if ($this->option('fix')) {
            $this->line('  <fg=cyan>▶  Running php artisan migrate ...</>');
            $this->newLine();

            $exit = $this->call('migrate', ['--force' => true]);

            if ($exit !== self::SUCCESS) {
                $this->newLine();
                $this->line('  <fg=red;options=bold>✘  migrate failed (exit ' . $exit . '). Check output above.</>');
                $this->newLine();
                return self::FAILURE;
            }

            $this->newLine();
            $this->line('  <fg=cyan>▶  Re-checking after migrate ...</>');
            $this->newLine();

            [$mt2, $mc2] = $this->runChecks();
            $remaining = count($mt2) + count($mc2);

            if ($remaining === 0) {
                $this->line('  <fg=green;options=bold>✔  All issues resolved — database is fully migrated.</>');
                $this->newLine();
                return self::SUCCESS;
            }

            // Still failing after migrate
            $this->line('  <fg=red;options=bold>✘  ' . $remaining . ' issue(s) still present after migrate:</>');
            foreach ($mt2 as $tbl) {
                $this->line("  <fg=red>  TABLE   {$tbl}</>");
            }
            foreach ($mc2 as [$tbl, $col]) {
                $this->line("  <fg=yellow>  COLUMN  {$tbl}.{$col}</>");
            }
            $this->newLine();
            $this->line('  <fg=yellow>Possible causes: migration not in codebase, rolled back, or DB permission issue.</>');
            $this->newLine();
            return self::FAILURE;
        }

        $this->line('  <fg=yellow>Tip: run with --fix to auto-migrate</>');
        $this->newLine();
        return self::FAILURE;
    }

    /**
     * Returns [missingTables[], missingColumns[['table','column'][]]].
     */
    private function runChecks(): array
    {
        $missingTables  = [];
        $missingColumns = [];
        $verbose        = $this->option('show-ok');

        foreach (self::EXPECTED as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
                if ($verbose) {
                    $this->line("  <fg=red>  ✘  TABLE   {$table}</>");
                }
                // No point checking columns if table missing
                continue;
            }

            if ($verbose) {
                $this->line("  <fg=green>  ✔  TABLE   {$table}</>");
            }

            foreach ($columns as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    $missingColumns[] = [$table, $col];
                    if ($verbose) {
                        $this->line("  <fg=yellow>      ✘  COLUMN  {$table}.{$col}</>");
                    }
                } elseif ($verbose) {
                    $this->line("  <fg=gray>      ✔  {$col}</>");
                }
            }
        }

        return [$missingTables, $missingColumns];
    }
}
