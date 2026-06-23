<?php

use App\Models\MealEntry;
use App\Models\MealEntryItem;
use App\Models\MealClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeEntryUser(string $role = 'manager'): User
{
    $user = User::factory()->create();
    $user->role = $role;
    $user->save();
    return $user;
}

function makeClient(User $user): MealClient
{
    return MealClient::create(['name' => 'Test Corp ' . uniqid(), 'active' => true, 'created_by' => $user->id]);
}

function baseEntryData(MealClient $client, array $overrides = []): array
{
    return array_merge([
        'meal_client_id' => $client->id,
        'entry_date'     => now()->toDateString(),
        'remarks'        => null,
        'items' => [
            ['meal_type' => 'breakfast', 'planned_count' => 50, 'actual_count' => 52],
            ['meal_type' => 'lunch',     'planned_count' => 80, 'actual_count' => 79],
            ['meal_type' => 'dinner',    'planned_count' => 30, 'actual_count' => null],
        ],
    ], $overrides);
}

// ── 1. Index ──────────────────────────────────────────────────────────────────

test('all roles can view entries index', function () {
    foreach (['admin', 'manager', 'employee'] as $role) {
        $user = makeEntryUser($role);
        $this->actingAs($user)->get(route('meal-register.entries.index'))->assertOk();
    }
});

// ── 2. Save (upsert) ──────────────────────────────────────────────────────────

test('manager can save (create) a daily meal entry with items', function () {
    $user   = makeEntryUser('manager');
    $client = makeClient($user);

    $this->actingAs($user)
        ->post(route('meal-register.entries.save'), baseEntryData($client))
        ->assertRedirect();

    $entry = MealEntry::where('meal_client_id', $client->id)->first();
    expect($entry)->not->toBeNull();
    expect($entry->items)->toHaveCount(3);
    expect($entry->created_by)->toBe($user->id);
});

test('admin can save a daily meal entry', function () {
    $user   = makeEntryUser('admin');
    $client = makeClient($user);

    $this->actingAs($user)
        ->post(route('meal-register.entries.save'), baseEntryData($client))
        ->assertRedirect();

    expect(MealEntry::where('meal_client_id', $client->id)->exists())->toBeTrue();
});

test('employee can save a daily meal entry', function () {
    $user   = makeEntryUser('employee');
    $client = makeClient($user);

    $this->actingAs($user)
        ->post(route('meal-register.entries.save'), baseEntryData($client))
        ->assertRedirect();

    expect(MealEntry::where('meal_client_id', $client->id)->exists())->toBeTrue();
});

test('duplicate same client+date results in update not duplicate (upsert)', function () {
    $user   = makeEntryUser('manager');
    $client = makeClient($user);
    $date   = now()->toDateString();

    // First save — creates
    $this->actingAs($user)->post(route('meal-register.entries.save'), baseEntryData($client, ['entry_date' => $date]));
    expect(MealEntry::where('meal_client_id', $client->id)->count())->toBe(1);

    // Second save same client+date — updates, still only 1 record
    $this->actingAs($user)->post(route('meal-register.entries.save'), baseEntryData($client, [
        'entry_date' => $date,
        'items' => [
            ['meal_type' => 'breakfast', 'planned_count' => 60, 'actual_count' => 58],
        ],
    ]));
    expect(MealEntry::where('meal_client_id', $client->id)->count())->toBe(1);
});

test('at least one item is required', function () {
    $user   = makeEntryUser('manager');
    $client = makeClient($user);

    $this->actingAs($user)
        ->post(route('meal-register.entries.save'), baseEntryData($client, ['items' => []]))
        ->assertSessionHasErrors('items');
});

test('invalid meal type is rejected', function () {
    $user   = makeEntryUser('manager');
    $client = makeClient($user);

    $data = baseEntryData($client);
    $data['items'][0]['meal_type'] = 'brunch'; // not in mealTypes

    $this->actingAs($user)
        ->post(route('meal-register.entries.save'), $data)
        ->assertSessionHasErrors('items.0.meal_type');
});

// ── 3. Load Entry API ─────────────────────────────────────────────────────────

test('load-entry returns null when no entry exists', function () {
    $user   = makeEntryUser('employee');
    $client = makeClient($user);

    $this->actingAs($user)
        ->getJson(route('meal-register.entries.load') . "?client_id={$client->id}&entry_date=" . now()->toDateString())
        ->assertOk()
        ->assertJson(['entry' => null]);
});

test('load-entry returns planned and actual counts', function () {
    $manager = makeEntryUser('manager');
    $client  = makeClient($manager);
    $date    = now()->toDateString();

    $entry = MealEntry::create([
        'meal_client_id' => $client->id,
        'entry_date'     => $date,
        'created_by'     => $manager->id,
    ]);
    $entry->items()->create(['meal_type' => 'breakfast', 'planned_count' => 60, 'actual_count' => 65, 'sort_order' => 1]);
    $entry->items()->create(['meal_type' => 'lunch',     'planned_count' => 80, 'actual_count' => null, 'sort_order' => 2]);

    $employee = makeEntryUser('employee');
    $this->actingAs($employee)
        ->getJson(route('meal-register.entries.load') . "?client_id={$client->id}&entry_date={$date}")
        ->assertOk()
        ->assertJsonPath('entry.id', $entry->id)
        ->assertJsonPath('entry.items.breakfast.planned', 60)
        ->assertJsonPath('entry.items.breakfast.actual', 65)
        ->assertJsonPath('entry.items.lunch.planned', 80)
        ->assertJsonPath('entry.items.lunch.actual', null);
});

// ── 4. Copy Yesterday API ─────────────────────────────────────────────────────

test('previous-day returns null when no prior entry exists', function () {
    $user   = makeEntryUser('manager');
    $client = makeClient($user);

    $this->actingAs($user)
        ->getJson(route('meal-register.entries.previous-day') . "?client_id={$client->id}&entry_date=" . now()->toDateString())
        ->assertOk()
        ->assertJson(['entry' => null]);
});

test('previous-day returns yesterday\'s planned counts', function () {
    $user      = makeEntryUser('manager');
    $client    = makeClient($user);
    $yesterday = now()->subDay()->toDateString();

    $entry = MealEntry::create([
        'meal_client_id' => $client->id,
        'entry_date'     => $yesterday,
        'created_by'     => $user->id,
    ]);
    $entry->items()->create(['meal_type' => 'lunch', 'planned_count' => 75, 'actual_count' => 80, 'sort_order' => 2]);

    $this->actingAs($user)
        ->getJson(route('meal-register.entries.previous-day') . "?client_id={$client->id}&entry_date=" . now()->toDateString())
        ->assertOk()
        ->assertJsonPath('entry.date', $yesterday)
        ->assertJsonPath('entry.items.0.meal_type', 'lunch')
        ->assertJsonPath('entry.items.0.planned_count', 75);
});

// ── 5. Show ───────────────────────────────────────────────────────────────────

test('any authenticated user can view an entry', function () {
    $user   = makeEntryUser('manager');
    $client = makeClient($user);

    $entry = MealEntry::create([
        'meal_client_id' => $client->id,
        'entry_date'     => now()->toDateString(),
        'created_by'     => $user->id,
    ]);

    foreach (['admin', 'manager', 'employee'] as $role) {
        $viewer = makeEntryUser($role);
        $this->actingAs($viewer)->get(route('meal-register.entries.show', $entry))->assertOk();
    }
});

// ── 6. Destroy ───────────────────────────────────────────────────────────────

test('admin can delete an entry', function () {
    $user   = makeEntryUser('admin');
    $client = makeClient($user);

    $entry = MealEntry::create([
        'meal_client_id' => $client->id,
        'entry_date'     => now()->toDateString(),
        'created_by'     => $user->id,
    ]);

    $this->actingAs($user)
        ->delete(route('meal-register.entries.destroy', $entry))
        ->assertRedirect(route('meal-register.entries.index'));

    $this->assertDatabaseMissing('meal_entries', ['id' => $entry->id]);
});

test('employee cannot delete an entry (403)', function () {
    $manager = makeEntryUser('manager');
    $client  = makeClient($manager);

    $entry = MealEntry::create([
        'meal_client_id' => $client->id,
        'entry_date'     => now()->toDateString(),
        'created_by'     => $manager->id,
    ]);

    $employee = makeEntryUser('employee');
    $this->actingAs($employee)
        ->delete(route('meal-register.entries.destroy', $entry))
        ->assertForbidden();
});

// ── 7. MealEntryItem variance helpers ────────────────────────────────────────

test('MealEntryItem variance and varianceClass helpers', function () {
    $item = new MealEntryItem(['planned_count' => 50, 'actual_count' => 55]);
    expect($item->variance())->toBe(5)
        ->and($item->varianceClass())->toBe('over');

    $item2 = new MealEntryItem(['planned_count' => 50, 'actual_count' => 45]);
    expect($item2->variance())->toBe(-5)
        ->and($item2->varianceClass())->toBe('under');

    $item3 = new MealEntryItem(['planned_count' => 50, 'actual_count' => 50]);
    expect($item3->variance())->toBe(0)
        ->and($item3->varianceClass())->toBe('equal');

    $item4 = new MealEntryItem(['planned_count' => 50, 'actual_count' => null]);
    expect($item4->variance())->toBeNull()
        ->and($item4->varianceClass())->toBe('neutral');
});

test('MealEntryItem diffClass alias is backward-compatible', function () {
    $item = new MealEntryItem(['planned_count' => 50, 'actual_count' => 55]);
    expect($item->diffClass())->toBe('dmr-over');

    $item2 = new MealEntryItem(['planned_count' => 50, 'actual_count' => 45]);
    expect($item2->diffClass())->toBe('dmr-under');

    $item3 = new MealEntryItem(['planned_count' => 50, 'actual_count' => null]);
    expect($item3->diffClass())->toBe('dmr-neutral');
});

// ── 8. MealEntry total helpers ────────────────────────────────────────────────

test('MealEntry totalPlanned and totalActual sum items', function () {
    $user   = makeEntryUser('manager');
    $client = makeClient($user);

    $entry = MealEntry::create([
        'meal_client_id' => $client->id,
        'entry_date'     => now()->toDateString(),
        'created_by'     => $user->id,
    ]);
    $entry->items()->createMany([
        ['meal_type' => 'breakfast', 'planned_count' => 30, 'actual_count' => 28, 'sort_order' => 1],
        ['meal_type' => 'lunch',     'planned_count' => 50, 'actual_count' => 55, 'sort_order' => 2],
        ['meal_type' => 'dinner',    'planned_count' => 20, 'actual_count' => null, 'sort_order' => 3],
    ]);
    $entry->load('items');

    expect($entry->totalPlanned())->toBe(100)
        ->and($entry->totalActual())->toBe(83); // 28+55+0
});

// ── 9. Reports page loads ─────────────────────────────────────────────────────

test('reports page is accessible to all roles', function () {
    foreach (['admin', 'manager', 'employee'] as $role) {
        $user = makeEntryUser($role);
        $this->actingAs($user)->get(route('meal-register.reports.index'))->assertOk();
    }
});
