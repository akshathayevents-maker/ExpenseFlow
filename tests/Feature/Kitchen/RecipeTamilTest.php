<?php

use App\Models\InventoryTransaction;
use App\Models\MaterialRequest;
use App\Models\Recipe;
use App\Models\RecipeSop;
use App\Services\Ai\RecipeAiClient;
use App\Services\Recipes\InvalidRecipeOutput;
use App\Services\Recipes\RecipeOutputValidator;
use App\Services\Recipes\StarterRecipeImporter;

beforeEach(function () {
    $this->admin = icUser('admin');
    app()->instance(RecipeAiClient::class, new FakeRecipeAi);
});

function tamilRecipe(array $over = []): Recipe
{
    $r = Recipe::create(array_merge(['name' => 'Test Pulao', 'name_ta' => 'சோதனை புலாவ்', 'category' => 'Main Course', 'yield_per_batch' => 100,
        'yield_unit' => 'people', 'created_by' => test()->admin->id, 'is_active' => true], $over));
    $r->ingredients()->create(['ingredient_name' => 'Basmati Rice', 'ingredient_name_ta' => 'பாஸ்மதி அரிசி', 'quantity_per_batch' => 10, 'unit' => 'kg', 'sort_order' => 0]);
    $r->ingredients()->create(['ingredient_name' => 'Ghee', 'quantity_per_batch' => 0.5, 'unit' => 'kg', 'sort_order' => 1]);
    $r->ingredients()->create(['ingredient_name' => 'Salt', 'quantity_note' => 'To taste', 'unit' => null, 'sort_order' => 2]);
    $r->sops()->create(['step_number' => 1, 'title' => 'Wash', 'title_ta' => 'கழுவுதல்', 'instruction' => 'Wash the rice thoroughly and soak for 30 minutes.', 'instruction_ta' => 'அரிசியை நன்றாக கழுவி 30 நிமிடங்கள் ஊற வைக்கவும்.']);
    $r->sops()->create(['step_number' => 2, 'title' => 'Temper', 'instruction' => 'Heat oil in a large vessel. Add cinnamon, cloves, cardamom and bay leaves.', 'instruction_ta' => null]);

    return $r->fresh(['ingredients', 'sops']);
}

it('imports the bilingual catalogue: every recipe and step has Tamil, quantities stay, nothing verified or stocked', function () {
    $set = app(StarterRecipeImporter::class)->recipes();
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.starter'))->assertRedirect();

    expect(Recipe::where('source', 'starter')->count())->toBe(count($set))->and(count($set))->toBeGreaterThanOrEqual(75)
        ->and(Recipe::where('source', 'starter')->whereNull('name_ta')->count())->toBe(0)
        ->and(RecipeSop::whereHas('recipe', fn ($q) => $q->where('source', 'starter'))->whereNull('instruction_ta')->count())->toBe(0)
        ->and(Recipe::whereNotNull('verified_at')->count())->toBe(0)
        ->and(InventoryTransaction::count())->toBe(0)->and(MaterialRequest::count())->toBe(0);

    $b = Recipe::where('name', 'Chicken Hyderabadi Biryani')->first();
    expect($b->name_ta)->toBe('சிக்கன் ஹைதராபாத் பிரியாணி')->and($b->food_type)->toBe('non_veg')
        ->and($b->ingredients->firstWhere('ingredient_name', 'Mint Leaves')->ingredient_name_ta)->toBe('புதினா')
        ->and($b->ingredients->firstWhere('ingredient_name', 'Ginger Garlic Paste')->ingredient_name_ta)->toBe('இஞ்சி பூண்டு விழுது')
        ->and($b->kitchen_notes_ta)->toContain('°C');
    foreach (['Chicken Seeraga Samba Biryani', 'Mutton Dum Biryani', 'Egg Biryani', 'Paneer Butter Masala', 'Dosa Batter', 'Chicken 65', 'Fish Curry', 'Gulab Jamun', 'Fruit Salad', 'Ghee Rice'] as $n) {
        expect(Recipe::where('name', $n)->exists())->toBeTrue("missing {$n}");
    }
});

it('is idempotent and only fills missing Tamil on existing recipes, preserving id, English, quantities and verification', function () {
    // an older import of "Idli" (English only), already verified by an admin
    $raw = collect(app(StarterRecipeImporter::class)->recipes())->firstWhere('name', 'Idli');
    $old = Recipe::create(['name' => 'Idli', 'category' => 'Breakfast', 'yield_per_batch' => 100, 'yield_unit' => 'people',
        'created_by' => $this->admin->id, 'source' => 'starter', 'verification_status' => 'needs_review']);
    foreach ($raw['steps'] as $n => $s) {
        $old->sops()->create(['step_number' => $n + 1, 'title' => $s['title'], 'instruction' => $s['instruction']]);
    }
    $old->ingredients()->create(['ingredient_name' => 'Idli Rice', 'quantity_per_batch' => 7, 'unit' => 'kg']);   // kitchen changed 6 → 7 kg
    // a manual recipe with the same name as a curated one but different steps
    $manual = Recipe::create(['name' => 'Rasam', 'category' => 'Main Course', 'yield_per_batch' => 50, 'yield_unit' => 'people', 'created_by' => $this->admin->id]);
    $manual->sops()->create(['step_number' => 1, 'title' => 'Our way', 'instruction' => 'Our own rasam method written by the head cook.']);

    $this->actingAs($this->admin);
    $r1 = app(StarterRecipeImporter::class)->import($this->admin);
    expect($r1['tamil_updated'])->toContain('Idli')->and($r1['skipped'])->toContain('Rasam')->and($r1['created'])->not->toContain('Idli');

    $old->refresh();
    expect($old->id)->toBe($old->id)->and($old->name_ta)->toBe('இட்லி')
        ->and($old->sops->first()->instruction_ta)->toContain('அரிசியை')
        ->and((float) $old->ingredients->first()->quantity_per_batch)->toBe(7.0)                 // kitchen's quantity kept
        ->and($old->ingredients->first()->ingredient_name_ta)->toBe('இட்லி அரிசி')
        ->and($old->verification_status)->toBe('needs_review');
    expect($manual->fresh()->sops->first()->instruction)->toBe('Our own rasam method written by the head cook.')
        ->and($manual->fresh()->sops->first()->instruction_ta)->toBeNull();                        // different steps → not overwritten

    $total = Recipe::count();
    $r2 = app(StarterRecipeImporter::class)->import($this->admin);
    expect(Recipe::count())->toBe($total)->and($r2['created'])->toBe([])->and($r2['tamil_updated'])->toBe([]);
});

it('scales ingredients for 50, 100, 250 and 500 and advises batch splitting', function () {
    $r = tamilRecipe();
    foreach ([50 => 5.0, 100 => 10.0, 250 => 25.0, 500 => 50.0] as $people => $rice) {
        $rows = collect($r->scaled($people));
        expect($rows[0]['quantity'])->toBe($rice)->and($rows[1]['quantity'])->toBe(0.5 * $people / 100)
            ->and($rows[2]['quantity'])->toBeNull()                   // "to taste" is not scaled
            ->and($rows[0]['per_serving'])->toBe(0.1);
    }
    expect($r->batchPlan(100)['batches'])->toBe(1)->and($r->batchPlan(100)['note'])->toBeNull()
        ->and($r->batchPlan(250)['batches'])->toBe(3)->and($r->batchPlan(500)['note'])->toContain('5 batches')
        ->and($r->batchPlan(500)['note_ta'])->toContain('5 தடவையாக');

    $this->actingAs($this->admin)->get(route('kitchen.recipes.print', [$r, 'people' => 500, 'lang' => 'both']))->assertOk()
        ->assertSee('For <b>500 people</b>', false)->assertSee('50 kg')->assertSee('2.5 kg')->assertSee('5 batches')
        ->assertSee('பாஸ்மதி அரிசி')->assertSee('100 g per serving');
});

it('shows English, Tamil and both, and marks missing Tamil instead of showing English', function () {
    $r = tamilRecipe();
    $this->actingAs($this->admin);
    $this->get(route('kitchen.recipes.show', $r))->assertOk()
        ->assertSee('data-rlang-root', false)->assertSee('தமிழ்')->assertSee('அரிசியை நன்றாக கழுவி 30 நிமிடங்கள் ஊற வைக்கவும்.')
        ->assertSee('Tamil instruction missing for this step')->assertSee('சோதனை புலாவ்');
    $en = $this->get(route('kitchen.recipes.print', [$r, 'lang' => 'en']))->getContent();
    expect($en)->toContain('Wash the rice thoroughly')->not->toContain('அரிசியை நன்றாக');
    $ta = $this->get(route('kitchen.recipes.print', [$r, 'lang' => 'ta']))->getContent();
    expect($ta)->toContain('அரிசியை நன்றாக கழுவி')->not->toContain('Wash the rice thoroughly')->toContain('Tamil missing');
    $both = $this->get(route('kitchen.recipes.print', [$r, 'lang' => 'both']))->getContent();
    expect($both)->toContain('Wash the rice thoroughly')->toContain('அரிசியை நன்றாக')
        ->and(strpos($both, 'Wash the rice thoroughly'))->toBeLessThan(strpos($both, 'அரிசியை நன்றாக'))   // grouped per step
        ->and(strpos($both, 'அரிசியை நன்றாக'))->toBeLessThan(strpos($both, 'Heat oil in a large vessel'));
    expect($both)->not->toContain('class="bar"><nav')->toContain('@media print');
});

it('saves Tamil step edits and keeps English and Tamil paired when steps are reordered', function () {
    $r = tamilRecipe();
    $payload = [
        'name' => 'Test Pulao', 'name_ta' => 'சோதனை புலாவ்', 'category' => 'Main Course', 'yield_per_batch' => 100, 'yield_unit' => 'people',
        'ingredients' => [['ingredient_name' => 'Basmati Rice', 'ingredient_name_ta' => 'பாஸ்மதி அரிசி', 'quantity_per_batch' => 10, 'unit' => 'kg']],
        'sops' => [   // step 2 moved first, Tamil added for it
            ['title' => 'Temper', 'title_ta' => 'தாளித்தல்', 'instruction' => 'Heat oil in a large vessel. Add cinnamon, cloves, cardamom and bay leaves.',
                'instruction_ta' => 'பெரிய பாத்திரத்தில் எண்ணெயை சூடாக்கி, பட்டை, கிராம்பு, ஏலக்காய் மற்றும் பிரியாணி இலை சேர்க்கவும்.', 'note_ta' => 'கவனம்'],
            ['title' => 'Wash', 'title_ta' => 'கழுவுதல்', 'instruction' => 'Wash the rice thoroughly and soak for 30 minutes.', 'instruction_ta' => 'அரிசியை நன்றாக கழுவி 30 நிமிடங்கள் ஊற வைக்கவும்.'],
        ],
    ];
    $this->actingAs($this->admin)->put(route('kitchen.recipes.update', $r), $payload)->assertSessionHasNoErrors();
    $steps = $r->fresh()->sops;
    expect($steps[0]->step_number)->toBe(1)->and($steps[0]->title)->toBe('Temper')
        ->and($steps[0]->instruction_ta)->toBe('பெரிய பாத்திரத்தில் எண்ணெயை சூடாக்கி, பட்டை, கிராம்பு, ஏலக்காய் மற்றும் பிரியாணி இலை சேர்க்கவும்.')
        ->and($steps[0]->note_ta)->toBe('கவனம்')
        ->and($steps[1]->instruction_ta)->toBe('அரிசியை நன்றாக கழுவி 30 நிமிடங்கள் ஊற வைக்கவும்.')
        ->and($r->fresh()->ingredients->first()->ingredient_name_ta)->toBe('பாஸ்மதி அரிசி')
        ->and($r->fresh()->languageGaps()['steps_without_tamil'])->toBe([]);

    $this->get(route('kitchen.recipes.edit', $r))->assertOk()
        ->assertSee('name="sops[0][instruction_ta]"', false)->assertSee('name="name_ta"', false)
        ->assertSee('name="ingredients[0][ingredient_name_ta]"', false)->assertSee('class="bl-switch"', false);
});

it('will not verify a recipe with missing Tamil unless the admin explicitly chooses English only', function () {
    $r = tamilRecipe(['is_active' => false, 'verification_status' => 'needs_review', 'source' => 'starter']);
    $this->actingAs($this->admin)->get(route('kitchen.recipes.show', $r))->assertOk()
        ->assertSee('Tamil missing for steps 2')->assertSee('Verify with English only');

    $this->post(route('kitchen.recipes.verify', $r), ['confirm' => '1'])->assertSessionHas('error');
    expect($r->fresh()->isVerified())->toBeFalse();

    $this->post(route('kitchen.recipes.verify', $r), ['confirm' => '1', 'allow_missing_tamil' => '1'])->assertSessionHas('success');
    expect($r->fresh()->isVerified())->toBeTrue()->and($r->fresh()->versions->first()->change_summary)->toContain('without complete Tamil');
});

it('lets kitchen staff print an active recipe from the calculator, never an inactive one', function () {
    $r = tamilRecipe();
    $draft = tamilRecipe(['name' => 'Draft Pulao', 'is_active' => false]);
    $this->actingAs($this->admin)->get(route('employee.kitchen.calculator.print', [$r, 'lang' => 'ta', 'people' => 50]))->assertOk()->assertSee('5 kg');
    $this->get(route('employee.kitchen.calculator.print', $draft))->assertNotFound();
});

it('flags AI recipes with incomplete Tamil for review and rejects non-vegetarian ingredients in veg dishes', function () {
    $v = app(RecipeOutputValidator::class);
    $raw = fakeRecipe('Lemon Rice');
    $raw['steps'][1]['instruction_ta'] = 'Boil water and add rice';          // English in the Tamil field is not a translation
    $out = $v->validate($raw, ['food_type' => 'veg']);
    expect($out['missing_tamil_steps'])->toBe([2])->and($out['steps'][1]['instruction_ta'])->toBeNull();

    $meat = fakeRecipe('Veg Fried Rice');
    $meat['ingredients'][] = ['name' => 'Chicken', 'quantity' => 2, 'unit' => 'kg', 'prep_note' => null, 'display_quantity' => null, 'approximate' => false, 'optional' => false];
    expect(fn () => $v->validate($meat, ['food_type' => 'veg']))->toThrow(InvalidRecipeOutput::class, 'contains chicken');

    $ai = new FakeRecipeAi;
    app()->instance(RecipeAiClient::class, $ai);
    $this->actingAs($this->admin)->post(route('kitchen.recipes.ai.store'), ['category' => 'Main Course', 'food_type' => 'veg', 'yield_people' => 100, 'dishes' => 'Lemon Rice']);
    $r = Recipe::where('name', 'Lemon Rice')->first();
    expect($r->name_ta)->toBe('சோதனை Lemon Rice')->and($r->sops->first()->instruction_ta)->not->toBeNull()
        ->and($r->ingredients->firstWhere('ingredient_name', 'Raw Rice')->ingredient_name_ta)->toBe('பச்சரிசி');
});
