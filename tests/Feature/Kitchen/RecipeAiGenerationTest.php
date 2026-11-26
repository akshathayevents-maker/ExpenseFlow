<?php

use App\Jobs\GenerateRecipeItemJob;
use App\Models\InventoryTransaction;
use App\Models\Recipe;
use App\Models\RecipeGenerationBatch;
use App\Models\RecipeGenerationItem;
use App\Models\RecipeVersion;
use App\Services\Ai\ClaudeRecipeAiClient;
use App\Services\Ai\RecipeAiClient;
use App\Services\Ai\RecipeAiException;
use App\Services\Inventory\ItemMatchingService;
use App\Services\Recipes\InvalidRecipeOutput;
use App\Services\Recipes\RecipeLibraryService;
use App\Services\Recipes\RecipeOutputValidator;

beforeEach(function () {
    $this->ai = new FakeRecipeAi;
    app()->instance(RecipeAiClient::class, $this->ai);
    icItem('Raw Rice', 'kg', 0);
    icItem('Sunflower Oil', 'litre', 0);
    icItem('Salt', 'kg', 0);
    $this->admin = icUser('admin');
});

it('validates AI output and rejects implausible or vague recipes', function () {
    $v = app(RecipeOutputValidator::class);
    $ok = $v->validate(fakeRecipe('Lemon Rice'), ['food_type' => 'veg']);
    expect($ok['ingredients'][1]['unit'])->toBe('litre')->and($ok['ingredients'][2]['unit'])->toBe('gram')
        ->and($ok['ingredients'][2]['is_approximate'])->toBeTrue()
        ->and($ok['ingredients'][4]['quantity_note'])->toBe('To garnish');

    $bad = fakeRecipe('X');
    $bad['ingredients'][0]['quantity'] = 900;                  // 900 kg rice for 100 people
    expect(fn () => $v->validate($bad))->toThrow(InvalidRecipeOutput::class);
    $vague = fakeRecipe('X');
    $vague['steps'][1]['instruction'] = 'Cook until done';
    expect(fn () => $v->validate($vague))->toThrow(InvalidRecipeOutput::class);
    expect(fn () => $v->validate(fakeRecipe('X'), ['food_type' => 'non_veg']))->toThrow(InvalidRecipeOutput::class);
    $noQty = fakeRecipe('X');
    foreach ($noQty['ingredients'] as &$i) {
        $i['quantity'] = null;
    }
    expect(fn () => $v->validate($noQty))->toThrow(InvalidRecipeOutput::class);
});

it('generates a batch from a dish list, maps ingredients, flags unmatched ones and never touches stock', function () {
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg', 'yield_people' => 100,
        'dishes' => "Lemon Rice\nTamarind Rice",
    ])->assertRedirect();

    $batch = RecipeGenerationBatch::first();
    expect($batch->status)->toBe('completed')->and($batch->generated_count)->toBe(2)->and($batch->requested_count)->toBe(2);
    $r = Recipe::where('name', 'Lemon Rice')->first();
    expect($r->source)->toBe('ai')->and($r->is_active)->toBeFalse()                       // hidden from kitchen until verified
        ->and($r->isVerified())->toBeFalse()->and($r->verification_status)->toBe('needs_review')   // unmatched ingredient
        ->and($r->ingredients)->toHaveCount(5)->and($r->sops)->toHaveCount(3);
    $byName = $r->ingredients->keyBy('ingredient_name');
    expect($byName['Raw Rice']->inventory_item_id)->not->toBeNull()
        ->and($byName['Sunflower Oil']->inventory_item_id)->not->toBeNull()
        ->and($byName['Dragonfruit Essence']->inventory_item_id)->toBeNull()
        ->and($byName['Dragonfruit Essence']->needs_mapping)->toBeTrue();
    expect(InventoryTransaction::count())->toBe(0);

    $this->get(route('kitchen.recipes.ai.progress', $batch))->assertOk()->assertJsonPath('status', 'completed')->assertJsonPath('generated', 2);
    $this->get(route('kitchen.recipes.ai.batch', $batch))->assertOk()->assertSee('Lemon Rice');
});

it('lets the AI plan dishes, skipping recipes that already exist', function () {
    Recipe::create(['name' => 'Curd Rice', 'category' => 'Main Course', 'yield_per_batch' => 50, 'yield_unit' => 'people', 'created_by' => $this->admin->id]);
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 50, 'count' => 5,
    ])->assertRedirect();

    $batch = RecipeGenerationBatch::first();
    expect($batch->items->pluck('dish_name')->all())->toBe(['Lemon Rice', 'Tamarind Rice'])->and($batch->status)->toBe('completed');
});

it('detects duplicates: same dish is skipped, a similar one is flagged for review', function () {
    Recipe::create(['name' => 'Upma', 'category' => 'Breakfast', 'yield_per_batch' => 50, 'yield_unit' => 'people', 'created_by' => $this->admin->id]);
    $lib = app(RecipeLibraryService::class);
    expect($lib->findDuplicate('upma')['exact']?->name)->toBe('Upma')
        ->and($lib->findDuplicate('Upma 2')['exact']?->name)->toBe('Upma')
        ->and($lib->findDuplicate('Rava Upma')['similar']?->name)->toBe('Upma')
        ->and($lib->findDuplicate('Sambar')['similar'])->toBeNull();

    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Breakfast', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => "UPMA\nRava Upma",
    ]);
    $batch = RecipeGenerationBatch::first();
    expect($batch->duplicate_count)->toBe(1)->and($batch->generated_count)->toBe(1)->and($batch->status)->toBe('completed');
    $rava = Recipe::where('name', 'Rava Upma')->first();
    expect($rava->duplicate_of_id)->toBe(Recipe::where('name', 'Upma')->value('id'))->and($rava->verification_status)->toBe('needs_review');
});

it('keeps successful recipes when part of a batch fails, and retries only the failed ones', function () {
    $this->ai->failNext['Tamarind Rice'] = [new RecipeAiException('key rejected')];   // non-retryable → fails at once
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => "Lemon Rice\nTamarind Rice",
    ]);
    $batch = RecipeGenerationBatch::first();
    expect($batch->status)->toBe('partially_completed')->and($batch->generated_count)->toBe(1)->and($batch->failed_count)->toBe(1)
        ->and(RecipeGenerationItem::where('status', 'failed')->value('error'))->toContain('key rejected');

    $this->post(route('kitchen.recipes.ai.retry', $batch))->assertSessionHas('success');
    expect($batch->fresh()->status)->toBe('completed')->and(Recipe::where('source', 'ai')->count())->toBe(2)
        ->and(array_count_values($this->ai->calls)['Lemon Rice'])->toBe(1);       // successful dish not regenerated
});

it('is idempotent: re-running a finished item job does not create a second recipe', function () {
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => 'Lemon Rice',
    ]);
    $item = RecipeGenerationItem::first();
    dispatch_sync(new GenerateRecipeItemJob($item->id));
    // a crash after saving but before marking the item done
    $item->update(['status' => 'processing', 'recipe_id' => null]);
    dispatch_sync(new GenerateRecipeItemJob($item->id));

    expect(Recipe::where('name', 'Lemon Rice')->count())->toBe(1)->and($this->ai->calls)->toBe(['Lemon Rice'])
        ->and($item->fresh()->status)->toBe('done')->and($item->fresh()->recipe_id)->not->toBeNull();
});

it('requires an explicit admin verification; verifying makes the recipe available and snapshots it', function () {
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => 'Lemon Rice',
    ]);
    $r = Recipe::where('name', 'Lemon Rice')->first();

    $this->get(route('kitchen.recipes.show', $r))->assertOk()->assertSee('AI Generated — Not Verified')->assertDontSee('Verified ✓ — trusted');
    $this->post(route('kitchen.recipes.verify', $r))->assertSessionHasErrors('confirm');
    expect($r->fresh()->isVerified())->toBeFalse();
    $this->patch(route('kitchen.recipes.toggle-active', $r))->assertSessionHas('error');   // cannot sneak it into the kitchen

    $this->post(route('kitchen.recipes.reviewed', $r))->assertSessionHas('success');
    expect($r->fresh()->verification_status)->toBe('reviewed');
    $this->post(route('kitchen.recipes.verify', $r), ['confirm' => '1', 'notes' => 'Tested for 120'])->assertSessionHas('success');

    $r->refresh();
    expect($r->isVerified())->toBeTrue()->and($r->is_active)->toBeTrue()->and($r->verified_by)->toBe($this->admin->id)
        ->and(RecipeVersion::where('recipe_id', $r->id)->where('event', 'verified')->count())->toBe(1);
    $this->get(route('kitchen.recipes.index'))->assertSee('Verified');
    $this->get(route('kitchen.recipes.show', $r))->assertSee('Verified ✓ — trusted recipe');
    $this->getJson(route('employee.kitchen.calculator.recipe', $r))->assertOk()->assertJsonPath('verified', true);
});

it('drops verification after a material edit, keeps the old version, but not for a wording change', function () {
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => 'Lemon Rice',
    ]);
    $r = Recipe::where('name', 'Lemon Rice')->first();
    $this->post(route('kitchen.recipes.verify', $r), ['confirm' => '1']);
    $payload = fn (array $over = []) => array_merge([
        'name' => 'Lemon Rice', 'category' => 'Main Course', 'description' => 'Tangy rice', 'yield_per_batch' => 100, 'yield_unit' => 'people',
        'ingredients' => $r->fresh()->ingredients->map(fn ($i) => $i->only(['ingredient_name', 'ingredient_name_ta', 'inventory_item_id', 'quantity_per_batch', 'quantity_note', 'unit', 'prep_note', 'is_optional', 'is_approximate']))->all(),
        'sops' => $r->fresh()->sops->map(fn ($s) => $s->only(['title', 'title_ta', 'instruction', 'instruction_ta', 'note', 'note_ta', 'duration_minutes']))->all(),
    ], $over);

    $this->put(route('kitchen.recipes.update', $r), $payload())->assertSessionHas('success');   // description only
    expect($r->fresh()->isVerified())->toBeTrue()->and($r->fresh()->revision)->toBe(1);

    $p = $payload();
    $p['ingredients'][0]['quantity_per_batch'] = 9;                                     // rice 8 → 9 kg
    $this->put(route('kitchen.recipes.update', $r), $p)->assertSessionHas('warning');
    $r->refresh();
    expect($r->isVerified())->toBeFalse()->and($r->verification_status)->toBe('needs_review')->and($r->revision)->toBe(2);
    $old = RecipeVersion::where('recipe_id', $r->id)->where('event', 'superseded')->first();
    expect($old->snapshot['ingredients'][0]['quantity_per_batch'])->toEqual(8.0)->and($old->verified_by)->toBe($this->admin->id);
    $this->delete(route('kitchen.recipes.destroy', $r))->assertSessionHas('error');      // history is never deleted
});

it('maps an unmatched ingredient and remembers it for next time', function () {
    $essence = icItem('Rose Essence', 'litre', 0);
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => 'Lemon Rice',
    ]);
    $r = Recipe::where('name', 'Lemon Rice')->first();
    $ing = $r->ingredients->firstWhere('ingredient_name', 'Dragonfruit Essence');
    $this->post(route('kitchen.recipes.map-ingredient', [$r, $ing]), ['inventory_item_id' => $essence->id])->assertSessionHas('success');
    expect($ing->fresh()->inventory_item_id)->toBe($essence->id)->and($ing->fresh()->needs_mapping)->toBeFalse()
        ->and(app(ItemMatchingService::class)->best('Dragonfruit Essence')['item_id'])->toBe($essence->id);
});

it('restricts generation and verification to admins', function () {
    $mgr = icUser('manager');
    $this->actingAs($mgr)->get(route('kitchen.recipes.ai.index'))->assertForbidden();
    $this->actingAs($mgr)->post(route('kitchen.recipes.ai.store'), ['category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'count' => 1])->assertForbidden();
    $r = Recipe::create(['name' => 'Rasam', 'category' => 'Main Course', 'yield_per_batch' => 50, 'yield_unit' => 'people', 'created_by' => $this->admin->id]);
    $this->actingAs($mgr)->post(route('kitchen.recipes.verify', $r), ['confirm' => '1'])->assertForbidden();
    $this->actingAs(icUser())->get(route('kitchen.recipes.review'))->assertForbidden();   // employees: no recipe admin at all
    expect($r->fresh()->isVerified())->toBeFalse();
});

it('reports a clear error when the AI key is missing and makes no call', function () {
    config(['services.anthropic.api_key' => null]);
    app()->instance(RecipeAiClient::class, new ClaudeRecipeAiClient);
    $this->actingAs($this->admin)->get(route('kitchen.recipes.ai.index'))->assertOk()->assertSee('API key missing')->assertSee('Not ready');
    $this->post(route('kitchen.recipes.ai.store'), ['category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'count' => 2])
        ->assertSessionHas('error');
    expect(RecipeGenerationBatch::count())->toBe(0);
});

it('shows the review queue with filters and the initial library plan', function () {
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), [
        'category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => 'Lemon Rice',
    ]);
    $this->get(route('kitchen.recipes.review'))->assertOk()->assertSee('Lemon Rice')->assertSee('not linked to stock');
    $this->get(route('kitchen.recipes.review', ['status' => 'verified']))->assertOk()->assertDontSee('Lemon Rice');
    $this->get(route('kitchen.recipes.ai.index'))->assertOk()->assertSee('Generate Initial Recipe Library')->assertSee('Breakfast');

    $this->post(route('kitchen.recipes.ai.initial-plan'), ['yield_people' => 100])->assertSessionHas('success');
    $planned = collect(config('recipe_generation.initial_plan'))->sum(fn ($g) => count($g['dishes']));
    // every plan dish gets an item, except Lemon Rice which is already in the library (+1 for its own item above)
    expect(RecipeGenerationItem::count())->toBe($planned - 1 + 1)
        ->and(Recipe::where('source', 'ai')->count())->toBeGreaterThan(50);
});
