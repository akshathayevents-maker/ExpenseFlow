<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_leave_allocations')) {
            Schema::create('employee_leave_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();

                $table->unsignedSmallInteger('period_year');
                // 0 = a yearly grant (whole-year allocation). 1-12 = a monthly
                // accrual for that specific month.
                //
                // Deliberately NOT nullable: Postgres (and this app is on
                // Postgres) treats NULL as distinct from NULL in a UNIQUE
                // constraint, so a nullable "yearly" sentinel would silently
                // defeat the idempotency guard below and allow duplicate
                // yearly grants. 0 is a real, comparable value instead.
                $table->unsignedTinyInteger('period_month')->default(0);

                $table->decimal('allocated_amount', 5, 1);

                // yearly_grant|monthly_accrual|manual_adjustment
                $table->string('source');

                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();

                // THE idempotency guard for both the yearly-grant path and the
                // monthly-accrual command: running either twice for the same
                // (user, type, period, source) is a guaranteed no-op at the DB level.
                $table->unique(
                    ['user_id', 'leave_type_id', 'period_year', 'period_month', 'source'],
                    'leave_alloc_unique_period'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_allocations');
    }
};
