<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_closing_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_closing_id')->constrained('daily_closings')->cascadeOnDelete();
            $table->string('action_type'); // created, expense_added, expense_edited, expense_removed, expense_restored, adjustment_added, adjustment_deleted, opening_balance_changed, finalized, recalculated, notes_updated
            $table->string('field_name')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['daily_closing_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closing_audits');
    }
};
