<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_policy_templates')) {
            Schema::create('leave_policy_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();

                // Only one template should have is_default=true at a time.
                // Enforced in LeavePolicyAssignmentService::setDefault(),
                // not a DB constraint — "no default at all" (all false) is
                // also a valid state (employees with no leave policy).
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);

                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policy_templates');
    }
};
