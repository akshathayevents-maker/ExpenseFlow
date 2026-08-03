<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_request_public_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_request_id')->constrained('event_requests')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['event_request_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_request_public_tokens');
    }
};
