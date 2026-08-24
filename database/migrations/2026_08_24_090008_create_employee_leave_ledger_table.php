<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_leave_ledger')) {
            Schema::create('employee_leave_ledger', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
                $table->date('entry_date');

                // allocation|usage|adjustment|reversal — balance is always
                // SUM(amount) over this table, never a stored running total.
                $table->string('type');
                $table->decimal('amount', 5, 1); // signed: +allocation, -usage, +/-adjustment/reversal

                // Polymorphic reference to the row that caused this entry
                // (an employee_leave_allocations.id or a leave_requests.id).
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();

                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'leave_type_id']);
                $table->index(['reference_type', 'reference_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_ledger');
    }
};
