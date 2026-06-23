<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_client_id')->constrained('meal_clients')->cascadeOnDelete();
            $table->date('entry_date');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('planned_updated_by')->nullable()->constrained('users');
            $table->foreignId('actual_updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['meal_client_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_entries');
    }
};
