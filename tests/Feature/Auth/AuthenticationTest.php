<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    // Post-login landing is role-based (see AuthenticatedSessionController::store),
    // not the generic /dashboard route: admins/managers land on the hall booking
    // calendar, everyone else (default role: employee) lands on their own
    // calendar. This has been the deliberate behavior since the "added new
    // flow" commit (143a55f, 2026-06-16) and predates this session's work.
    $response->assertRedirect(route('employee.hall.bookings.calendar', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
