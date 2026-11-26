<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI recipe generation + review / verification + version history.
 *
 * recipes.verification_status: unverified (manual, legacy) | ai_generated | needs_review | reviewed | verified | archived.
 * A recipe is only "Verified" after an explicit admin action (verified_by / verified_at).
 * recipe_versions keeps a full snapshot (recipe + ingredients + steps) of every verified version and of the
 * version replaced by a material edit, so history never changes silently.
 * recipe_generation_batches / _items: one item = one dish to generate = one idempotent unit of work.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_generation_batches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('created_by')->constrained('users');
            $t->string('status', 30)->default('pending');   // pending|processing|completed|partially_completed|failed|cancelled
            $t->string('label', 150)->nullable();
            $t->json('config');                               // category, cuisine, food_type, yield, dishes, plan group…
            $t->unsignedInteger('requested_count')->default(0);
            $t->unsignedInteger('generated_count')->default(0);
            $t->unsignedInteger('failed_count')->default(0);
            $t->unsignedInteger('duplicate_count')->default(0);
            $t->string('provider', 40)->nullable();
            $t->string('model', 80)->nullable();
            $t->text('error')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->index(['status', 'created_at']);
        });

        Schema::create('recipe_generation_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('recipe_generation_batch_id')->constrained()->cascadeOnDelete();
            $t->string('dish_name', 200);
            $t->string('dish_key', 200);                      // spelling-insensitive key: one item per dish per batch
            $t->string('status', 30)->default('pending');     // pending|processing|done|duplicate|failed|cancelled
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->foreignId('recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
            $t->text('error')->nullable();
            $t->string('request_id', 120)->nullable();        // provider request id, for support
            $t->timestamps();
            $t->unique(['recipe_generation_batch_id', 'dish_key']);
            $t->index('status');
        });

        Schema::table('recipes', function (Blueprint $t) {
            $t->string('verification_status', 20)->default('unverified')->after('is_active');
            $t->foreignId('verified_by')->nullable()->after('verification_status')->constrained('users')->nullOnDelete();
            $t->timestamp('verified_at')->nullable()->after('verified_by');
            $t->text('verification_notes')->nullable()->after('verified_at');
            $t->foreignId('reviewed_by')->nullable()->after('verification_notes')->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $t->string('source', 20)->default('manual')->after('reviewed_at');   // manual | ai | standard
            $t->string('cuisine', 60)->nullable()->after('category');
            $t->string('food_type', 20)->nullable()->after('cuisine');          // veg | non_veg | egg | vegan
            $t->unsignedInteger('revision')->default(1)->after('food_type');    // bumps on every material change
            $t->foreignId('generation_item_id')->nullable()->after('source')->constrained('recipe_generation_items')->nullOnDelete();
            $t->foreignId('duplicate_of_id')->nullable()->after('generation_item_id')->constrained('recipes')->nullOnDelete();
            $t->json('ai_meta')->nullable()->after('duplicate_of_id');
            $t->index('verification_status');
        });

        Schema::table('recipe_ingredients', function (Blueprint $t) {
            $t->boolean('is_approximate')->default(false)->after('quantity_note');
            $t->unsignedTinyInteger('match_confidence')->nullable()->after('inventory_item_id');
            $t->boolean('needs_mapping')->default(false)->after('match_confidence');
        });

        Schema::create('recipe_versions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('revision');
            $t->string('event', 30);                          // verified | superseded
            $t->json('snapshot');
            $t->string('change_summary', 500)->nullable();
            $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('verified_at')->nullable();
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('created_at')->nullable();
            $t->index(['recipe_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_versions');
        Schema::table('recipe_ingredients', fn (Blueprint $t) => $t->dropColumn(['is_approximate', 'match_confidence', 'needs_mapping']));
        Schema::table('recipes', function (Blueprint $t) {
            $t->dropConstrainedForeignId('duplicate_of_id');
            $t->dropConstrainedForeignId('generation_item_id');
            $t->dropConstrainedForeignId('reviewed_by');
            $t->dropConstrainedForeignId('verified_by');
            $t->dropIndex(['verification_status']);
            $t->dropColumn(['verification_status', 'verified_at', 'verification_notes', 'reviewed_at', 'source', 'cuisine', 'food_type', 'revision', 'ai_meta']);
        });
        Schema::dropIfExists('recipe_generation_items');
        Schema::dropIfExists('recipe_generation_batches');
    }
};
