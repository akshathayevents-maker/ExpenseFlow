<?php

use App\Models\EmployeeAttendance;
use App\Models\Recipe;
use App\Models\User;

function markKitchenGateAttendance(User $user): void
{
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => \Carbon\Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
}

test('kitchen calculator page loads for an employee', function () {
    $user = User::factory()->create();
    markKitchenGateAttendance($user);
    Recipe::create([
        'name' => 'Masala Dosa', 'category' => 'South Indian', 'yield_per_batch' => 10,
        'yield_unit' => 'plates', 'is_active' => true, 'created_by' => $user->id,
    ]);

    $this->actingAs($user->fresh())->get(route('employee.kitchen.calculator'))
        ->assertOk()
        ->assertSee('Kitchen Calculator')
        ->assertSee('Masala Dosa');
});

test('recipe search combobox markup and accessibility attributes are present', function () {
    $user = User::factory()->create();
    markKitchenGateAttendance($user);

    $response = $this->actingAs($user->fresh())->get(route('employee.kitchen.calculator'));
    $response->assertOk();

    $response->assertSee('id="kcComboInput"', false);
    $response->assertSee('role="combobox"', false);
    $response->assertSee('aria-autocomplete="list"', false);
    $response->assertSee('aria-controls="kcComboList"', false);
    $response->assertSee('id="kcComboDropdown"', false);
    $response->assertSee('role="listbox"', false);
});

test('recipe suggestion dropdown uses keyboard-aware positioning driven by visualViewport, not a fixed spacer hack', function () {
    $user = User::factory()->create();
    markKitchenGateAttendance($user);

    $response = $this->actingAs($user->fresh())->get(route('employee.kitchen.calculator'));
    $response->assertOk();
    $html = $response->getContent();

    // The fix must be driven by the visual viewport API...
    expect($html)->toContain('window.visualViewport');
    expect($html)->toContain('kcPositionCombo');
    // ...and must not be a lazy fixed-height spacer/permanent-shift hack.
    expect($html)->not->toContain('padding-bottom:500px');
    expect($html)->not->toContain('padding-bottom: 500px');
});

test('recipe suggestion dropdown repositions on keyboard resize and page scroll, not on every keystroke', function () {
    $user = User::factory()->create();
    markKitchenGateAttendance($user);

    $response = $this->actingAs($user->fresh())->get(route('employee.kitchen.calculator'));
    $html = $response->getContent();

    expect($html)->toContain("visualViewport.addEventListener('resize'");
    expect($html)->toContain("visualViewport.addEventListener('scroll'");
    // comboInput's own 'input' handler (typing) must not itself scroll the page.
    $inputHandlerStart = strpos($html, "comboInput.addEventListener('input'");
    $inputHandlerEnd = strpos($html, '});', $inputHandlerStart);
    $inputHandlerBody = substr($html, $inputHandlerStart, $inputHandlerEnd - $inputHandlerStart);
    expect($inputHandlerBody)->not->toContain('scrollIntoView');
});

test('recipe suggestion items keep a comfortable touch target', function () {
    $css = null;
    $response = null;
    $user = User::factory()->create();
    markKitchenGateAttendance($user);

    $response = $this->actingAs($user->fresh())->get(route('employee.kitchen.calculator'));
    $html = $response->getContent();

    expect($html)->toContain('min-height: 48px');
});

test('empty search state shows a clear no-recipes message', function () {
    $user = User::factory()->create();
    markKitchenGateAttendance($user);

    $response = $this->actingAs($user->fresh())->get(route('employee.kitchen.calculator'));
    $html = $response->getContent();

    expect($html)->toContain('No recipes match');
});

test('inactive recipes are not exposed on the calculator page', function () {
    $user = User::factory()->create();
    markKitchenGateAttendance($user);
    Recipe::create([
        'name' => 'Retired Recipe', 'yield_per_batch' => 5, 'yield_unit' => 'kg',
        'is_active' => false, 'created_by' => $user->id,
    ]);

    $this->actingAs($user->fresh())->get(route('employee.kitchen.calculator'))
        ->assertOk()
        ->assertDontSee('Retired Recipe');
});
