<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Client-facing details
            $table->string('client_name')->nullable();
            $table->string('client_mobile')->nullable();
            $table->string('client_email')->nullable();
            $table->string('event_name')->nullable();
            $table->date('event_date')->nullable();
            $table->string('meal_type')->nullable(); // breakfast|lunch|dinner|reception|high_tea
            $table->unsignedInteger('guest_count')->nullable();
            $table->string('menu_type')->nullable(); // veg|non_veg|both
            $table->text('special_instructions')->nullable();

            // Lifecycle
            $table->string('status')->default('draft');
            // draft|submitted|under_review|need_changes|resubmitted|approved|rejected|scheduled
            $table->text('admin_comment')->nullable();

            // Pricing (computed from selected items, snapshot at last save)
            $table->decimal('per_person_cost', 10, 2)->default(0);
            $table->decimal('estimated_total', 12, 2)->default(0);

            // Calendar integration (nullable — only set once approved)
            $table->foreignId('hall_booking_id')->nullable()->constrained('hall_bookings')->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('event_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_requests');
    }
};
