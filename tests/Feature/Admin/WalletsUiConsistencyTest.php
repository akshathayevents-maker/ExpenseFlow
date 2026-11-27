<?php

use App\Models\User;
use App\Services\WalletService;

test('admin dashboard still renders', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
});

test('admin wallets index renders', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    app(WalletService::class)->getOrCreate($employee);

    $this->actingAs($admin)->get(route('admin.wallets.index'))
        ->assertOk()
        ->assertSee('Wallets')
        ->assertSee($employee->name);
});

test('admin wallet show page renders', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    app(WalletService::class)->getOrCreate($employee);

    $this->actingAs($admin)->get(route('admin.wallets.show', $employee))->assertOk();
});

test('non-admin cannot access admin wallets pages', function () {
    $employee = User::factory()->create();

    $this->actingAs($employee->fresh())->get(route('admin.wallets.index'))->assertForbidden();
});

test('wallets index uses the list-page health strip with working filters', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.wallets.index'));
    $response->assertOk();

    $response->assertSee('class="wl-q"', false);
    $response->assertSee(route('admin.wallets.index', ['health' => 'critical']), false);
    $response->assertSee(route('admin.wallets.index', ['health' => 'low']), false);
    $response->assertDontSee('ef-wlt-kpi"', false);
});

test('wallets index no longer contains off-brand purple or invented orange colors', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.wallets.index'));
    $html = $response->getContent();
    // Scope to the Wallets page content only — admin-layout's shared
    // global toast styling (unrelated, app-wide, out of scope) also uses
    // #d97706 for its own warning icon and must not trip this check.
    $walletBlock = substr($html, strpos($html, '<div class="wl">'));

    // Purple (Tailwind violet) that was used for the "Pending Reimb." pill.
    expect($walletBlock)->not->toContain('139,92,246');
    expect($walletBlock)->not->toContain('c4b5fd');
    // Invented orange tiers that had no equivalent app-wide token.
    expect($walletBlock)->not->toContain('ea580c');
    expect($walletBlock)->not->toContain('d97706');
    expect($walletBlock)->not->toContain('b45309');
});

test('wallets index primary action buttons use the application emerald/dark tokens, not a wallet-specific gold CTA', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    app(WalletService::class)->getOrCreate($employee);

    $response = $this->actingAs($admin)->get(route('admin.wallets.index'));
    $html = $response->getContent();

    expect($html)->toContain('wl-btn --sm --main');          // Add funds = dark primary
    expect($html)->not->toContain('--gold"');
    expect($html)->toContain('ef-btn ef-btn-dark');
});

test('wallet show page no longer declares its own duplicate design-token variables', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    app(WalletService::class)->getOrCreate($employee);

    $response = $this->actingAs($admin)->get(route('admin.wallets.show', $employee));
    $html = $response->getContent();

    expect($html)->not->toContain('--wfin-emerald');
    expect($html)->not->toContain('--wfin-gold');
    expect($html)->not->toContain('--wfin-danger');
    expect($html)->not->toContain('--wfin-amber');
});

test('wallets index shows 30-day activity and last activity per wallet without per-row queries', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);   // audit log needs a signed-in user
    $svc = app(WalletService::class);
    foreach (range(1, 6) as $i) {
        $e = User::factory()->create(['role' => 'employee']);
        $w = $svc->getOrCreate($e);
        $svc->credit($w, 1000, 'Advance', $admin);
        $svc->debit($w->fresh(), 250, 'Fuel', $admin);
    }
    \Illuminate\Support\Facades\DB::enableQueryLog();
    $this->actingAs($admin)->get(route('admin.wallets.index'))->assertOk()
        ->assertSee('−₹250 spent')->assertSee('+₹1,000 added')->assertSee('Spent · last 30 days');
    expect(count(\Illuminate\Support\Facades\DB::getQueryLog()))->toBeLessThan(25);
});
