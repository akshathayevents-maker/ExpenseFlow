<?php

use App\Models\MaterialRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function mrlRequest(User $by, string $status, array $extra = []): MaterialRequest
{
    return MaterialRequest::create($extra + ['department' => 'kitchen', 'source' => 'other', 'required_date' => now()->addDay()->toDateString(),
        'status' => $status, 'requested_by' => $by->id, 'preparation_name' => 'Sambar']);
}

it('shows role queues with counts and the next action per row', function () {
    $admin = icUser('admin');
    $emp = icUser();
    mrlRequest($emp, 'submitted', ['preparation_name' => 'Rava Upma']);
    mrlRequest($emp, 'issued', ['preparation_name' => 'Lemon Rice']);

    $html = $this->actingAs($admin)->get(route('inventory.material-requests.index', ['tab' => 'approve']))->assertOk()
        ->assertSee('data-k="approve"', false)->assertSee('Review')->getContent();
    expect(substr_count($html, 'data-request'))->toBe(1)->and($html)->toContain('Rava Upma');
    // requester gets "Record usage" for their issued request
    $this->actingAs($emp)->get(route('inventory.material-requests.index'))->assertOk()
        ->assertSee('Record usage')->assertDontSee('data-k="approve"', false);
});

it('searches by dish, person and MR reference and filters by date and department', function () {
    $admin = icUser('admin');
    $a = mrlRequest($admin, 'submitted', ['preparation_name' => 'Pongal', 'department' => 'hall', 'required_date' => '2026-12-10']);
    mrlRequest($admin, 'submitted', ['preparation_name' => 'Kesari', 'required_date' => '2026-12-01']);

    foreach (['pongal', $a->reference(), (string) $a->id] as $q) {
        $html = $this->actingAs($admin)->get(route('inventory.material-requests.index', ['tab' => 'all', 'q' => $q]))->getContent();
        expect(substr_count($html, 'data-request'))->toBe(1, "q=$q")->and($html)->toContain('Pongal');
    }
    $html = $this->actingAs($admin)->get(route('inventory.material-requests.index', ['tab' => 'all', 'from' => '2026-12-05']))->getContent();
    expect(substr_count($html, 'data-request'))->toBe(1);
    $html = $this->actingAs($admin)->get(route('inventory.material-requests.index', ['tab' => 'all', 'department' => 'hall']))->getContent();
    expect(substr_count($html, 'data-request'))->toBe(1);
    $this->actingAs($admin)->get(route('inventory.material-requests.index', ['department' => 'nope']))->assertSessionHasErrors('department');
});

it('flags overdue open requests and keeps the query count flat', function () {
    $admin = icUser('admin');
    mrlRequest($admin, 'submitted', ['required_date' => now()->subDays(2)->toDateString()]);
    foreach (range(1, 12) as $i) {
        mrlRequest($admin, 'submitted');
    }
    DB::enableQueryLog();
    $this->actingAs($admin)->get(route('inventory.material-requests.index', ['tab' => 'all']))->assertOk()->assertSee('1 past their needed date');
    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(20);
});
