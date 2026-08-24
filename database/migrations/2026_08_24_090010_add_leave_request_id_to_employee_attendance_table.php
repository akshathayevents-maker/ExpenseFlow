<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendance', 'leave_request_id')) {
                // Set only when source=leave_approval. On leave reversal, only
                // rows matching (leave_request_id = X AND source = 'leave_approval')
                // are reverted — a row later corrected by an admin (source=admin)
                // keeps its leave_request_id for provenance but is never touched
                // by a reversal.
                $table->foreignId('leave_request_id')->nullable()
                    ->after('correction_reason')
                    ->constrained('leave_requests')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendance', function (Blueprint $table) {
            if (Schema::hasColumn('employee_attendance', 'leave_request_id')) {
                $table->dropConstrainedForeignId('leave_request_id');
            }
        });
    }
};
