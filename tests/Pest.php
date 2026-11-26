<?php

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Ai\RecipeAiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

// LeaveRequest::status/reviewed_by/reviewed_at/review_note are server-only
// and excluded from $fillable — fixtures across multiple test files need to
// set them directly (e.g. a pre-approved leave request), so this shared
// helper does the fill()+forceFill()+save() split once instead of repeating
// it at every call site.
function hardenedLeaveRequest(array $attrs): LeaveRequest
{
    $fillableKeys = ['user_id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day', 'half_day_period', 'days_requested', 'reason'];
    $leaveRequest = new LeaveRequest;
    $leaveRequest->fill(array_intersect_key($attrs, array_flip($fillableKeys)));
    $leaveRequest->forceFill(array_diff_key($attrs, array_flip($fillableKeys)));
    $leaveRequest->save();

    return $leaveRequest;
}

// ── Kitchen inventory control fixtures ────────────────────────────────────
function icUser(string $role = 'employee', bool $storekeeper = false): User
{
    $u = User::factory()->create();
    $u->role = $role;
    $u->is_active = true;
    $u->is_storekeeper = $storekeeper;
    $u->save();

    return $u;
}

function icItem(string $name, string $unit = 'kg', float $stock = 0, float $min = 0): InventoryItem
{
    $cat = InventoryCategory::first() ?? InventoryCategory::create(['name' => 'Groceries', 'is_active' => true]);

    return InventoryItem::create([
        'name' => $name, 'inventory_category_id' => $cat->id, 'unit' => $unit,
        'current_stock' => $stock, 'minimum_stock' => $min, 'status' => 'active',
    ]);
}

// ── AI recipe generation fixtures (no network) ─────────────────────────────
/** In-memory AI: no network. Queue failures per dish with ->failNext[dish] = [exceptions…]. */
class FakeRecipeAi implements RecipeAiClient
{
    public array $calls = [];

    public array $failNext = [];

    public array $plan = ['Lemon Rice', 'Tamarind Rice', 'Curd Rice'];

    public function planDishes(array $config, int $count, array $exclude): array
    {
        return array_slice(array_values(array_diff($this->plan, $exclude)), 0, $count);
    }

    public function generateRecipe(string $dish, array $config): array
    {
        $this->calls[] = $dish;
        if (! empty($this->failNext[$dish])) {
            throw array_shift($this->failNext[$dish]);
        }

        return ['recipe' => fakeRecipe($dish, (int) ($config['yield_people'] ?? 100)), 'request_id' => 'req_test', 'model' => 'fake-model'];
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function providerName(): string
    {
        return 'fake';
    }

    public function modelName(): string
    {
        return 'fake-model';
    }
}

function fakeRecipe(string $dish, int $people = 100): array
{
    return [
        'name' => $dish, 'name_ta' => 'சோதனை '.$dish, 'kitchen_notes' => 'Cook in batches of 100.', 'kitchen_notes_ta' => '100 பேர் அளவில் சமைக்கவும்.', 'description' => "{$dish} for catering.", 'category' => 'Main Course', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg',
        'yield_per_batch' => $people, 'yield_unit' => 'people', 'prep_time_minutes' => 30, 'cook_time_minutes' => 45,
        'ingredients' => [
            ['name' => 'Raw Rice', 'quantity' => 8, 'unit' => 'kg', 'prep_note' => 'washed twice', 'display_quantity' => null, 'approximate' => false, 'optional' => false],
            ['name' => 'Sunflower Oil', 'quantity' => 0.8, 'unit' => 'L', 'prep_note' => null, 'display_quantity' => null, 'approximate' => false, 'optional' => false],
            ['name' => 'Salt', 'quantity' => 150, 'unit' => 'grams', 'prep_note' => null, 'display_quantity' => null, 'approximate' => true, 'optional' => false],
            ['name' => 'Dragonfruit Essence', 'quantity' => 20, 'unit' => 'ml', 'prep_note' => null, 'display_quantity' => null, 'approximate' => false, 'optional' => false],
            ['name' => 'Coriander Leaves', 'quantity' => null, 'unit' => null, 'prep_note' => 'chopped', 'display_quantity' => 'To garnish', 'approximate' => true, 'optional' => true],
        ],
        'steps' => [
            ['title' => 'Wash and soak', 'instruction' => 'Wash the rice twice in clean water until the water runs almost clear, then drain well.', 'instruction_ta' => 'அரிசியை இரண்டு முறை கழுவி நன்றாக வடிக்கவும்.', 'duration_minutes' => 15],
            ['title' => 'Cook the rice', 'instruction' => 'Boil 16 L water on high heat, add rice and salt, cook on medium for 18 minutes until grains are soft but separate.', 'instruction_ta' => '16 லிட்டர் தண்ணீர் கொதித்ததும் அரிசி, உப்பு சேர்த்து 18 நிமிடம் வேக விடவும்.', 'duration_minutes' => 20],
            ['title' => 'Temper and mix', 'instruction' => 'Heat oil on medium, add the tempering until it splutters, fold gently into the rice without breaking grains. Hold above 65 °C.', 'instruction_ta' => 'எண்ணெயில் தாளித்து சாதத்தில் மெதுவாக கலக்கவும். 65 °C க்கு மேல் வைக்கவும்.', 'duration_minutes' => 10],
        ],
    ];
}
