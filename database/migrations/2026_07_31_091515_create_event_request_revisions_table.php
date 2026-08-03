<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_request_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_request_id')->constrained('event_requests')->cascadeOnDelete();

            // created|client_submitted|admin_modified|need_changes|client_resubmitted|approved|rejected
            $table->string('action');
            $table->string('actor_type')->default('admin'); // client|admin
            $table->string('actor_name')->nullable();
            $table->text('comment')->nullable();
            $table->json('snapshot')->nullable(); // full request + item state at this point

            $table->timestamp('created_at')->useCurrent();

            $table->index('event_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_request_revisions');
    }
};
