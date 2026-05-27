<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expense_requests')) {
            Schema::create('expense_requests', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->text('notes')->nullable();
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['pending', 'approved', 'rejected', 'paid', 'completed'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_requests');
    }
};
