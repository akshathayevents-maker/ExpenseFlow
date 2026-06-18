<?php

namespace Tests\Feature\Menu;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User   { return User::factory()->create(['role' => 'admin']); }
    private function manager(): User { return User::factory()->create(['role' => 'manager']); }
    private function employee(): User{ return User::factory()->create(['role' => 'employee']); }

    // ── Access: admin only ─────────────────────────────────────────────────

    public function test_guest_cannot_access_menu_items(): void
    {
        $this->get(route('menu.items.index'))->assertRedirect(route('login'));
    }

    public function test_employee_is_forbidden(): void
    {
        $this->actingAs($this->employee())
             ->get(route('menu.items.index'))
             ->assertForbidden();
    }

    public function test_manager_is_forbidden(): void
    {
        $this->actingAs($this->manager())
             ->get(route('menu.items.index'))
             ->assertForbidden();
    }

    public function test_admin_can_access_menu_items_index(): void
    {
        $this->actingAs($this->admin())
             ->get(route('menu.items.index'))
             ->assertOk();
    }

    // ── Verify every route denies manager ────────────────────────────────

    public function test_manager_cannot_create_item(): void
    {
        $this->actingAs($this->manager())
             ->post(route('menu.items.store'), ['category_key' => 'sweet', 'item_en' => 'X', 'item_ta' => 'X'])
             ->assertForbidden();
    }

    public function test_manager_cannot_update_item(): void
    {
        $item = MenuItem::factory()->create();
        $this->actingAs($this->manager())
             ->put(route('menu.items.update', $item), ['category_key' => 'sweet', 'item_en' => 'X', 'item_ta' => 'X'])
             ->assertForbidden();
    }

    public function test_manager_cannot_toggle_item(): void
    {
        $item = MenuItem::factory()->create();
        $this->actingAs($this->manager())
             ->patch(route('menu.items.toggle', $item))
             ->assertForbidden();
    }

    public function test_manager_cannot_delete_item(): void
    {
        $item = MenuItem::factory()->create();
        $this->actingAs($this->manager())
             ->delete(route('menu.items.destroy', $item))
             ->assertForbidden();
    }

    public function test_manager_cannot_search_items(): void
    {
        $this->actingAs($this->manager())
             ->getJson(route('menu.items.search', ['q' => 'a']))
             ->assertForbidden();
    }

    // ── CRUD ───────────────────────────────────────────────────────────────

    public function test_admin_can_create_menu_item(): void
    {
        $this->actingAs($this->admin())
             ->post(route('menu.items.store'), [
                 'category_key' => 'sweet',
                 'item_en'      => 'Kesari',
                 'item_ta'      => 'கேசரி',
             ])
             ->assertRedirect(route('menu.items.index'));

        $this->assertDatabaseHas('menu_items', [
            'item_en'      => 'Kesari',
            'item_ta'      => 'கேசரி',
            'category_key' => 'sweet',
            'category_en'  => 'Sweet',
            'category_ta'  => 'ஸ்வீட்',
            'is_active'    => 1,
        ]);
    }

    public function test_menu_item_requires_english_and_tamil(): void
    {
        $this->actingAs($this->admin())
             ->post(route('menu.items.store'), ['category_key' => 'sweet', 'item_en' => '', 'item_ta' => ''])
             ->assertSessionHasErrors(['item_en', 'item_ta']);
    }

    public function test_category_key_must_be_valid(): void
    {
        $this->actingAs($this->admin())
             ->post(route('menu.items.store'), ['category_key' => 'invalid_cat', 'item_en' => 'Test', 'item_ta' => 'டெஸ்ட்'])
             ->assertSessionHasErrors(['category_key']);
    }

    public function test_item_en_max_200_chars(): void
    {
        $this->actingAs($this->admin())
             ->post(route('menu.items.store'), [
                 'category_key' => 'sweet',
                 'item_en'      => str_repeat('A', 201),
                 'item_ta'      => 'டெஸ்ட்',
             ])
             ->assertSessionHasErrors(['item_en']);
    }

    public function test_admin_can_update_menu_item(): void
    {
        $item = MenuItem::factory()->create(['category_key' => 'sweet', 'category_en' => 'Sweet', 'category_ta' => 'ஸ்வீட்', 'item_en' => 'Kesari', 'item_ta' => 'கேசரி']);

        $this->actingAs($this->admin())
             ->put(route('menu.items.update', $item), ['category_key' => 'dessert', 'item_en' => 'Kesari Updated', 'item_ta' => 'கேசரி'])
             ->assertRedirect(route('menu.items.index'));

        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'item_en' => 'Kesari Updated', 'category_key' => 'dessert']);
    }

    public function test_admin_can_toggle_menu_item_status(): void
    {
        $item = MenuItem::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
             ->patch(route('menu.items.toggle', $item))
             ->assertRedirect();

        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'is_active' => 0]);
    }

    public function test_admin_can_delete_menu_item(): void
    {
        $item = MenuItem::factory()->create();

        $this->actingAs($this->admin())
             ->delete(route('menu.items.destroy', $item))
             ->assertRedirect(route('menu.items.index'));

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    // ── Tamil auto-translate ───────────────────────────────────────────────

    public function test_translate_endpoint_returns_suggestion(): void
    {
        $res = $this->actingAs($this->admin())
                    ->getJson(route('menu.items.translate', ['q' => 'Kesari']))
                    ->assertOk()
                    ->json();

        $this->assertArrayHasKey('tamil', $res);
        $this->assertSame('கேசரி', $res['tamil']);
    }

    public function test_translate_returns_null_for_unknown_term(): void
    {
        $res = $this->actingAs($this->admin())
                    ->getJson(route('menu.items.translate', ['q' => 'xyzzy_totally_unknown_word_12345']))
                    ->assertOk()
                    ->json();

        $this->assertNull($res['tamil']);
    }

    public function test_translate_returns_null_for_empty_input(): void
    {
        $res = $this->actingAs($this->admin())
                    ->getJson(route('menu.items.translate', ['q' => '']))
                    ->assertOk()
                    ->json();

        $this->assertNull($res['tamil']);
    }

    public function test_translate_composes_multi_word(): void
    {
        $res = $this->actingAs($this->admin())
                    ->getJson(route('menu.items.translate', ['q' => 'Chicken Biryani']))
                    ->assertOk()
                    ->json();

        $this->assertNotNull($res['tamil']);
        $this->assertStringContainsString('கோழி', $res['tamil']);
    }

    public function test_manager_cannot_access_translate(): void
    {
        $this->actingAs($this->manager())
             ->getJson(route('menu.items.translate', ['q' => 'Kesari']))
             ->assertForbidden();
    }

    // ── Search ─────────────────────────────────────────────────────────────

    public function test_search_returns_matching_items(): void
    {
        MenuItem::factory()->create(['item_en' => 'Kesari', 'item_ta' => 'கேசரி', 'is_active' => true]);
        MenuItem::factory()->create(['item_en' => 'Laddu',  'item_ta' => 'லட்டு',  'is_active' => true]);

        $res = $this->actingAs($this->admin())
                    ->getJson(route('menu.items.search', ['q' => 'kes']))
                    ->assertOk()
                    ->json();

        $this->assertCount(1, $res);
        $this->assertEquals('Kesari', $res[0]['item_en']);
    }

    public function test_search_supports_tamil_query(): void
    {
        MenuItem::factory()->create(['item_en' => 'Kesari', 'item_ta' => 'கேசரி', 'is_active' => true]);

        $res = $this->actingAs($this->admin())
                    ->getJson(route('menu.items.search', ['q' => 'கே']))
                    ->assertOk()
                    ->json();

        $this->assertCount(1, $res);
    }

    public function test_search_excludes_inactive_items(): void
    {
        MenuItem::factory()->create(['item_en' => 'Kesari', 'item_ta' => 'கேசரி', 'is_active' => false]);

        $res = $this->actingAs($this->admin())
                    ->getJson(route('menu.items.search', ['q' => 'kes']))
                    ->assertOk()
                    ->json();

        $this->assertCount(0, $res);
    }

    // ── Audit logging ──────────────────────────────────────────────────────

    public function test_creating_item_writes_audit_log(): void
    {
        $this->actingAs($this->admin())
             ->post(route('menu.items.store'), ['category_key' => 'sweet', 'item_en' => 'Mysorepak', 'item_ta' => 'மைசூர்பாக்']);

        $this->assertDatabaseHas('audit_logs', ['module' => 'menu_item', 'action' => 'created', 'reference_label' => 'Mysorepak']);
    }

    public function test_deleting_item_writes_audit_log(): void
    {
        $item = MenuItem::factory()->create(['item_en' => 'Mysorepak']);

        $this->actingAs($this->admin())
             ->delete(route('menu.items.destroy', $item));

        $this->assertDatabaseHas('audit_logs', ['module' => 'menu_item', 'action' => 'deleted', 'reference_label' => 'Mysorepak']);
    }
}
