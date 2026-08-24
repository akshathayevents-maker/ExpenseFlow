<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained()->restrictOnDelete();

                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_half_day')->default(false);
                $table->string('half_day_period')->nullable(); // first_half|second_half
                $table->decimal('days_requested', 5, 1);
                $table->text('reason');

                // pending|approved|rejected|cancelled
                $table->string('status')->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_note')->nullable();

                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['start_date', 'end_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
