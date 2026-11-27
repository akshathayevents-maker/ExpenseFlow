<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the Store dashboard and request/purchase lists at volume (thousands of rows).
 * PostgreSQL does not index foreign keys by itself; these back the per-request line loads,
 * per-purchase line loads and the "unusual usage to check" count. Additive, safe to re-run.
 */
return new class extends Migration
{
    private const INDEXES = [
        ['material_request_items', ['material_request_id'], 'mri_request_idx'],
        ['purchase_items', ['purchase_id'], 'pi_purchase_idx'],
        ['material_requests', ['reconciliation_status'], 'mr_reconciliation_idx'],
        ['inventory_items', ['status', 'minimum_stock'], 'ii_status_min_idx'],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as [$table, $cols, $name]) {
            if (Schema::hasTable($table) && ! Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $t) => $t->index($cols, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as [$table, , $name]) {
            if (Schema::hasTable($table) && Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
            }
        }
    }
};
