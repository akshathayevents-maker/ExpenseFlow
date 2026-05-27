<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('action');   // approved, rejected, deleted, credited, debited, adjusted, etc.
                $table->string('module');   // expense_request, wallet, inventory, payment, etc.
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_label')->nullable(); // human-readable label
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['module', 'reference_id']);
                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
