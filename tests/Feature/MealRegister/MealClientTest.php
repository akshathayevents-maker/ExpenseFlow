<?php

use App\Models\MealClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMealUser(string $role = 'manager'): User
{
    $user = User::factory()->create();
    $user->role = $role;
    $user->save();
    return $user;
}

// ── 1. Index is accessible to all authenticated roles ─────────────────────────

test('admin can view meal clients index', function () {
    $user = makeMealUser('admin');
    $this->actingAs($user)->get(route('meal-register.clients.index'))->assertOk();
});

test('manager can view meal clients index', function () {
    $user = makeMealUser('manager');
    $this->actingAs($user)->get(route('meal-register.clients.index'))->assertOk();
});

test('employee can view meal clients index', function () {
    $user = makeMealUser('employee');
    $this->actingAs($user)->get(route('meal-register.clients.index'))->assertOk();
});

test('unauthenticated user is redirected from clients index', function () {
    $this->get(route('meal-register.clients.index'))->assertRedirect(route('login'));
});

// ── 2. Create / Store ─────────────────────────────────────────────────────────

test('manager can create a meal client', function () {
    $user = makeMealUser('manager');
    $this->actingAs($user)
        ->post(route('meal-register.clients.store'), [
            'name'           => 'TCS Chennai',
            'contact_person' => 'Ravi Kumar',
            'mobile'         => '9876543210',
            'address'        => 'Sholinganallur',
        ])->assertRedirect(route('meal-register.clients.index'));

    $this->assertDatabaseHas('meal_clients', ['name' => 'TCS Chennai', 'active' => 1]);
});

test('admin can create a meal client', function () {
    $user = makeMealUser('admin');
    $this->actingAs($user)
        ->post(route('meal-register.clients.store'), ['name' => 'Infosys'])
        ->assertRedirect(route('meal-register.clients.index'));

    $this->assertDatabaseHas('meal_clients', ['name' => 'Infosys']);
});

test('employee cannot access the create client form', function () {
    $user = makeMealUser('employee');
    $this->actingAs($user)->get(route('meal-register.clients.create'))->assertForbidden();
});

test('employee cannot store a client', function () {
    $user = makeMealUser('employee');
    $this->actingAs($user)
        ->post(route('meal-register.clients.store'), ['name' => 'Wipro'])
        ->assertForbidden();
});

test('client name is required', function () {
    $user = makeMealUser('manager');
    $this->actingAs($user)
        ->post(route('meal-register.clients.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('client name must be unique', function () {
    $user = makeMealUser('manager');
    MealClient::create(['name' => 'Wipro', 'created_by' => $user->id]);

    $this->actingAs($user)
        ->post(route('meal-register.clients.store'), ['name' => 'Wipro'])
        ->assertSessionHasErrors('name');
});

// ── 3. Edit / Update ──────────────────────────────────────────────────────────

test('manager can update a client', function () {
    $user   = makeMealUser('manager');
    $client = MealClient::create(['name' => 'Old Name', 'created_by' => $user->id]);

    $this->actingAs($user)
        ->put(route('meal-register.clients.update', $client), ['name' => 'New Name'])
        ->assertRedirect(route('meal-register.clients.show', $client));

    $this->assertDatabaseHas('meal_clients', ['id' => $client->id, 'name' => 'New Name']);
});

// ── 4. Toggle active ─────────────────────────────────────────────────────────

test('manager can toggle client active status', function () {
    $user   = makeMealUser('manager');
    $client = MealClient::create(['name' => 'ABC Corp', 'active' => true, 'created_by' => $user->id]);

    $this->actingAs($user)
        ->patch(route('meal-register.clients.toggle', $client))
        ->assertRedirect();

    $this->assertDatabaseHas('meal_clients', ['id' => $client->id, 'active' => 0]);
});

test('scopeActive returns only active clients', function () {
    $user = makeMealUser('admin');
    MealClient::create(['name' => 'Active Co', 'active' => true,  'created_by' => $user->id]);
    MealClient::create(['name' => 'Inactive Co', 'active' => false, 'created_by' => $user->id]);

    $active = MealClient::active()->pluck('name');
    expect($active)->toContain('Active Co')
        ->and($active)->not->toContain('Inactive Co');
});
