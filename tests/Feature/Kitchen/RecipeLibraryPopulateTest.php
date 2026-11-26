<?php

use App\Models\InventoryTransaction;
use App\Models\Recipe;
use App\Models\RecipeGenerationBatch;
use App\Models\RecipeGenerationItem;
use App\Services\Ai\ClaudeRecipeAiClient;
use App\Services\Ai\RecipeAiClient;
use App\Services\QueueHealth;
use App\Services\Recipes\RecipeGenerationService;
use App\Services\Recipes\RecipeOutputValidator;
use App\Services\Recipes\StarterRecipeImporter;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->admin = icUser('admin');
    app()->instance(RecipeAiClient::class, new FakeRecipeAi);
});

it('shows how to populate an empty library', function () {
    $this->actingAs($this->admin)->get(route('kitchen.recipes.index'))->assertOk()
        ->assertSee('No recipes yet')->assertSee('Add starter recipes')->assertSee('Review &amp; link stock items', false);
});

it('imports the curated starter set as unverified, hidden from the kitchen, and is safe to re-run', function () {
    $total = count(app(StarterRecipeImporter::class)->recipes());
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.starter'))->assertRedirect(route('kitchen.recipes.review'));

    expect(Recipe::where('source', 'starter')->count())->toBe($total)
        ->and(Recipe::where('source', 'starter')->where('is_active', true)->count())->toBe(0)
        ->and(Recipe::where('source', 'starter')->whereNotNull('verified_at')->count())->toBe(0)
        ->and(Recipe::where('source', 'starter')->where('verification_status', 'needs_review')->count())->toBe($total)
        ->and(InventoryTransaction::count())->toBe(0);
    $idli = Recipe::where('name', 'Idli')->first();
    expect($idli->ingredients->count())->toBeGreaterThan(3)->and($idli->sops->count())->toBeGreaterThan(2)
        ->and((float) $idli->yield_per_batch)->toBe(100.0);

    $this->post(route('kitchen.recipes.ai.starter'));
    expect(Recipe::where('source', 'starter')->count())->toBe($total);                         // nothing duplicated
    $this->artisan('recipes:import-starter')->expectsOutputToContain("0 imported, {$total} already in the library")->assertSuccessful();

    $this->get(route('kitchen.recipes.review', ['q' => 'Idli']))->assertOk()->assertSee('Idli')->assertSee('Starter set');
    $this->get(route('kitchen.recipes.show', $idli))->assertOk()->assertSee('Curated starter recipe — Not Verified');
    $this->patch(route('kitchen.recipes.toggle-active', $idli))->assertSessionHas('error');      // only verification activates it
});

it('lists unverified recipes in the library with their status and filters by verification; archived only on request', function () {
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.starter'));
    $idli = Recipe::where('name', 'Idli')->first();
    $this->post(route('kitchen.recipes.verify', $idli), ['confirm' => '1']);
    $rasam = Recipe::where('name', 'Rasam')->first();
    $this->post(route('kitchen.recipes.archive', $rasam));

    $this->get(route('kitchen.recipes.index'))->assertOk()->assertSee('Sambar')->assertSee('Curated starter · not verified')->assertDontSee('>Rasam<', false);
    $html = $this->get(route('kitchen.recipes.index', ['verification' => 'verified']))->assertOk()->getContent();
    expect($html)->toContain('Idli')->not->toContain('Sambar');
    $this->get(route('kitchen.recipes.index', ['verification' => 'archived']))->assertOk()->assertSee('Rasam');
    $this->get(route('kitchen.recipes.index', ['verification' => 'to_review']))->assertOk()->assertSee('Sambar')->assertDontSee('>Idli<', false);
    $this->get(route('kitchen.recipes.review', ['status' => 'verified']))->assertOk()->assertSee('Idli');
});

it('explains a missing key and a missing worker instead of pretending to generate', function () {
    config(['services.anthropic.api_key' => null, 'queue.default' => 'database']);
    app()->instance(RecipeAiClient::class, new ClaudeRecipeAiClient);
    Cache::forget(QueueHealth::HEARTBEAT_KEY);

    $this->actingAs($this->admin)->get(route('kitchen.recipes.ai.index'))->assertOk()
        ->assertSee('API key missing')->assertSee('No worker seen')->assertSee('php artisan queue:work --timeout=600')
        ->assertSee('Add ANTHROPIC_API_KEY to enable AI generation.')->assertDontSee('sk-ant');

    QueueHealth::beat();
    $this->get(route('kitchen.recipes.ai.index'))->assertSee('Worker running');
});

it('detects a stuck batch and re-queues it without losing written recipes', function () {
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => 'Lemon Rice',
    ]);
    $batch = RecipeGenerationBatch::first();
    // simulate a second dish whose job was lost (worker died / never ran)
    $lost = RecipeGenerationItem::create(['recipe_generation_batch_id' => $batch->id, 'dish_name' => 'Tamarind Rice', 'dish_key' => 'tamarind', 'status' => 'processing']);
    RecipeGenerationItem::whereKey($lost->id)->update(['updated_at' => now()->subMinutes(30)]);
    $batch->update(['status' => 'processing', 'requested_count' => 2]);

    $gen = app(RecipeGenerationService::class);
    expect($gen->stalledCount($batch->fresh()))->toBe(1);
    $this->get(route('kitchen.recipes.ai.batch', $batch))->assertOk()->assertSee('dishes look stuck');
    $this->getJson(route('kitchen.recipes.ai.progress', $batch))->assertJsonPath('stalled', 1);

    $this->post(route('kitchen.recipes.ai.retry', $batch))->assertSessionHas('success');
    expect($lost->fresh()->status)->toBe('done')->and($batch->fresh()->status)->toBe('completed')
        ->and(Recipe::where('name', 'Lemon Rice')->count())->toBe(1);
});

it('keeps the curated dataset valid', function () {
    $v = app(RecipeOutputValidator::class);
    $names = [];
    foreach (app(StarterRecipeImporter::class)->recipes() as $r) {
        $v->validate($r, ['food_type' => $r['food_type']]);
        $names[] = mb_strtolower($r['name']);
    }
    expect(count($names))->toBe(count(array_unique($names)))->and(count($names))->toBeGreaterThanOrEqual(25);
});
