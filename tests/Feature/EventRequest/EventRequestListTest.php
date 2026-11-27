<?php

use App\Models\EventRequest;

function erlReq(array $a = []): EventRequest
{
    return EventRequest::forceCreate(array_merge([
        'client_name' => 'Client '.uniqid(), 'client_mobile' => '9876543210', 'event_name' => 'Wedding lunch',
        'event_date' => now()->addDays(5)->toDateString(), 'meal_type' => 'lunch', 'guest_count' => 150,
        'menu_type' => 'veg', 'status' => 'submitted', 'estimated_total' => 45000,
    ], $a));
}

it('shows status tiles, rows and the coming-up rail', function () {
    erlReq(['client_name' => 'Ravi Kumar']);
    erlReq(['client_name' => 'Meena S', 'status' => 'scheduled']);
    erlReq(['client_name' => 'Old Party', 'status' => 'rejected', 'event_date' => now()->subDays(3)->toDateString()]);

    $res = $this->actingAs(icUser('admin'))->get(route('admin.event-requests.index'))->assertOk()
        ->assertSee('Ravi Kumar')->assertSee('Meena S')->assertSee('Old Party')->assertSee('₹45,000')
        ->assertSee('1 waiting for review')
        ->assertSeeInOrder(['data-k="all"', '3', 'data-k="submitted"', '1'], false);

    expect(substr_count($res->getContent(), 'data-request='))->toBe(3)
        ->and(substr_count($res->getContent(), 'data-upcoming'))->toBe(1);
});

it('filters by status and shows filtered empty state', function () {
    erlReq(['client_name' => 'Ravi Kumar']);
    erlReq(['client_name' => 'Meena S', 'status' => 'scheduled']);
    $u = icUser('admin');

    $this->actingAs($u)->get(route('admin.event-requests.index', ['status' => 'scheduled']))
        ->assertSee('Meena S')->assertDontSee('Ravi Kumar');
    $this->actingAs($u)->get(route('admin.event-requests.index', ['status' => 'rejected']))
        ->assertSee('No event requests match');
});

it('shows empty state without requests', function () {
    $this->actingAs(icUser('admin'))->get(route('admin.event-requests.index'))->assertOk()->assertSee('No event requests yet');
});
