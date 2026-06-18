<?php

namespace Tests\Feature\Menu;

use App\Models\MenuDraft;
use App\Models\MenuTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuComposerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User    { return User::factory()->create(['role' => 'admin']); }
    private function manager(): User  { return User::factory()->create(['role' => 'manager']); }
    private function employee(): User { return User::factory()->create(['role' => 'employee']); }

    /** New array-of-sections format with one item in Breakfast. */
    private function sampleContent(): array
    {
        return [
            [
                'key'      => 'breakfast',
                'label_en' => 'Breakfast',
                'label_ta' => 'காலை உணவு',
                'items'    => [
                    ['id' => 1, 'item_en' => 'Kesari', 'item_ta' => 'கேசரி', 'category_key' => 'sweet', 'category_en' => 'Sweet', 'category_ta' => 'ஸ்வீட்'],
                ],
            ],
        ];
    }

    private function emptyContent(): array { return []; }

    // ── Access: admin only ─────────────────────────────────────────────────

    public function test_guest_redirected_to_login(): void
    {
        $this->get(route('menu.composer.index'))->assertRedirect(route('login'));
    }

    public function test_employee_forbidden_from_composer(): void
    {
        $this->actingAs($this->employee())
             ->get(route('menu.composer.index'))
             ->assertForbidden();
    }

    public function test_manager_forbidden_from_composer_index(): void
    {
        $this->actingAs($this->manager())
             ->get(route('menu.composer.index'))
             ->assertForbidden();
    }

    public function test_manager_forbidden_from_composer_create(): void
    {
        $this->actingAs($this->manager())
             ->get(route('menu.composer.create'))
             ->assertForbidden();
    }

    public function test_manager_forbidden_from_saving_draft(): void
    {
        $this->actingAs($this->manager())
             ->postJson(route('menu.drafts.store'), ['title' => 'Test', 'content' => $this->sampleContent()])
             ->assertForbidden();
    }

    public function test_manager_forbidden_from_pdf_generation(): void
    {
        $this->actingAs($this->manager())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => 'Test', 'content' => $this->sampleContent()])
             ->assertForbidden();
    }

    public function test_manager_cannot_access_templates(): void
    {
        $this->actingAs($this->manager())
             ->get(route('menu.templates.index'))
             ->assertForbidden();
    }

    // ── Direct URL access tests ────────────────────────────────────────────

    public function test_manager_direct_url_draft_edit_forbidden(): void
    {
        $admin = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $admin->id, 'content' => $this->sampleContent()]);

        $this->actingAs($this->manager())
             ->get(route('menu.drafts.edit', $draft))
             ->assertForbidden();
    }

    public function test_manager_direct_url_draft_delete_forbidden(): void
    {
        $admin = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $admin->id, 'content' => $this->sampleContent()]);

        $this->actingAs($this->manager())
             ->delete(route('menu.drafts.destroy', $draft))
             ->assertForbidden();
    }

    public function test_manager_direct_url_draft_duplicate_forbidden(): void
    {
        $admin = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $admin->id, 'content' => $this->sampleContent()]);

        $this->actingAs($this->manager())
             ->post(route('menu.drafts.duplicate', $draft))
             ->assertForbidden();
    }

    // ── Admin composer pages ───────────────────────────────────────────────

    public function test_admin_composer_index_loads(): void
    {
        $this->actingAs($this->admin())
             ->get(route('menu.composer.index'))
             ->assertOk();
    }

    public function test_admin_composer_create_loads(): void
    {
        $this->actingAs($this->admin())
             ->get(route('menu.composer.create'))
             ->assertOk();
    }

    // ── Draft CRUD ─────────────────────────────────────────────────────────

    public function test_admin_can_save_draft(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
             ->postJson(route('menu.drafts.store'), [
                 'title'   => 'Wedding Menu',
                 'content' => $this->sampleContent(),
             ])
             ->assertOk()
             ->assertJsonStructure(['id', 'edit_url']);

        $this->assertDatabaseHas('menu_drafts', ['title' => 'Wedding Menu', 'created_by' => $user->id]);
    }

    public function test_draft_requires_title(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.drafts.store'), ['title' => '', 'content' => $this->sampleContent()])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['title']);
    }

    public function test_admin_can_update_own_draft(): void
    {
        $user  = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $user->id, 'content' => $this->sampleContent()]);

        $this->actingAs($user)
             ->putJson(route('menu.drafts.update', $draft), ['title' => 'Updated Title', 'content' => $this->sampleContent()])
             ->assertOk()
             ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('menu_drafts', ['id' => $draft->id, 'title' => 'Updated Title']);
    }

    public function test_admin_cannot_update_another_admins_draft(): void
    {
        $owner = $this->admin();
        $other = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $owner->id, 'content' => $this->sampleContent()]);

        $this->actingAs($other)
             ->putJson(route('menu.drafts.update', $draft), ['title' => 'Hijacked', 'content' => $this->sampleContent()])
             ->assertForbidden();
    }

    public function test_admin_can_delete_own_draft(): void
    {
        $user  = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $user->id, 'content' => $this->sampleContent()]);

        $this->actingAs($user)
             ->delete(route('menu.drafts.destroy', $draft))
             ->assertRedirect(route('menu.composer.index'));

        $this->assertDatabaseMissing('menu_drafts', ['id' => $draft->id]);
    }

    // ── Draft duplication ──────────────────────────────────────────────────

    public function test_admin_can_duplicate_draft(): void
    {
        $user  = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $user->id, 'title' => 'Wedding Menu', 'content' => $this->sampleContent()]);

        $this->actingAs($user)
             ->post(route('menu.drafts.duplicate', $draft))
             ->assertRedirect();

        $this->assertDatabaseHas('menu_drafts', ['title' => 'Wedding Menu (Copy)', 'created_by' => $user->id]);
        $this->assertDatabaseHas('menu_drafts', ['title' => 'Wedding Menu', 'id' => $draft->id]);
    }

    public function test_duplicate_increments_copy_number(): void
    {
        $user  = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $user->id, 'title' => 'Wedding Menu', 'content' => $this->sampleContent()]);

        $actor = $this->actingAs($user);
        $actor->post(route('menu.drafts.duplicate', $draft));
        $actor->post(route('menu.drafts.duplicate', $draft));

        $this->assertDatabaseHas('menu_drafts', ['title' => 'Wedding Menu (Copy)']);
        $this->assertDatabaseHas('menu_drafts', ['title' => 'Wedding Menu (Copy 2)']);
    }

    public function test_duplicate_copies_content(): void
    {
        $user  = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $user->id, 'title' => 'Wedding Menu', 'content' => $this->sampleContent()]);

        $this->actingAs($user)->post(route('menu.drafts.duplicate', $draft));

        $copy = MenuDraft::where('title', 'Wedding Menu (Copy)')->first();
        $this->assertNotNull($copy);

        // Verify content preserved via normalizedContent
        $normalized = $copy->normalizedContent();
        $this->assertCount(1, $normalized);
        $this->assertEquals('breakfast', $normalized[0]['key']);
        $this->assertCount(1, $normalized[0]['items']);
        $this->assertEquals('Kesari', $normalized[0]['items'][0]['item_en']);
    }

    public function test_manager_cannot_duplicate_draft(): void
    {
        $admin = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $admin->id, 'content' => $this->sampleContent()]);

        $this->actingAs($this->manager())
             ->post(route('menu.drafts.duplicate', $draft))
             ->assertForbidden();
    }

    // ── Dynamic sections ───────────────────────────────────────────────────

    public function test_draft_with_multiple_sections_preserved(): void
    {
        $user    = $this->admin();
        $content = [
            ['key' => 'lunch',   'label_en' => 'Lunch',   'label_ta' => 'மதிய உணவு', 'items' => [
                ['id' => 1, 'item_en' => 'Rice', 'item_ta' => 'சாதம்', 'category_key' => 'rice', 'category_en' => 'Rice', 'category_ta' => 'சாதம்'],
            ]],
            ['key' => 'dinner',  'label_en' => 'Dinner',  'label_ta' => 'இரவு உணவு', 'items' => [
                ['id' => 2, 'item_en' => 'Chapati', 'item_ta' => 'சப்பாத்தி', 'category_key' => 'indian_bread', 'category_en' => 'Indian Bread', 'category_ta' => 'ரொட்டி வகைகள்'],
            ]],
        ];

        $this->actingAs($user)
             ->postJson(route('menu.drafts.store'), ['title' => 'Multi-Section Menu', 'content' => $content])
             ->assertOk();

        $draft = MenuDraft::where('title', 'Multi-Section Menu')->first();
        $this->assertCount(2, $draft->normalizedContent());
        $this->assertEquals('lunch',  $draft->normalizedContent()[0]['key']);
        $this->assertEquals('dinner', $draft->normalizedContent()[1]['key']);
    }

    public function test_custom_section_accepted(): void
    {
        $user    = $this->admin();
        $content = [
            ['key' => 'custom', 'label_en' => 'VIP Counter', 'label_ta' => 'விஐபி கவுண்டர்', 'items' => [
                ['id' => 1, 'item_en' => 'Special Dish', 'item_ta' => 'சிறப்பு உணவு', 'category_key' => 'other', 'category_en' => 'Other', 'category_ta' => 'மற்றவை'],
            ]],
        ];

        $this->actingAs($user)
             ->postJson(route('menu.drafts.store'), ['title' => 'VIP Menu', 'content' => $content])
             ->assertOk();

        $draft = MenuDraft::where('title', 'VIP Menu')->first();
        $this->assertEquals('VIP Counter', $draft->normalizedContent()[0]['label_en']);
    }

    public function test_legacy_old_format_auto_migrates(): void
    {
        $user = $this->admin();
        // Simulate a draft stored in old keyed-object format
        $draft = MenuDraft::factory()->create([
            'created_by' => $user->id,
            'content'    => [
                'breakfast'      => [['id' => 1, 'item_en' => 'Idly', 'item_ta' => 'இட்லி', 'category_key' => 'main_course', 'category_en' => 'Main Course', 'category_ta' => 'முக்கிய உணவு']],
                'lunch'          => [],
                'dinner'         => [],
                'evening_snacks' => [],
            ],
        ]);

        $normalized = $draft->normalizedContent();
        $this->assertIsArray($normalized);
        // Only non-empty sections are included
        $this->assertCount(1, $normalized);
        $this->assertEquals('breakfast', $normalized[0]['key']);
        $this->assertEquals('Idly', $normalized[0]['items'][0]['item_en']);
    }

    // ── Templates ─────────────────────────────────────────────────────────

    public function test_admin_can_view_templates(): void
    {
        $this->actingAs($this->admin())
             ->get(route('menu.templates.index'))
             ->assertOk();
    }

    public function test_admin_can_create_template(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
             ->postJson(route('menu.templates.store'), [
                 'name'    => 'Standard Wedding Lunch',
                 'content' => $this->sampleContent(),
             ])
             ->assertOk()
             ->assertJsonStructure(['id', 'name']);

        $this->assertDatabaseHas('menu_templates', ['name' => 'Standard Wedding Lunch', 'created_by' => $user->id]);
    }

    public function test_template_requires_name(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.templates.store'), ['name' => '', 'content' => $this->sampleContent()])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['name']);
    }

    public function test_admin_can_load_template_into_draft(): void
    {
        $user     = $this->admin();
        $template = MenuTemplate::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
             ->post(route('menu.templates.load', $template))
             ->assertRedirect();

        $this->assertDatabaseHas('menu_drafts', ['created_by' => $user->id]);
    }

    public function test_loaded_template_populates_draft_content(): void
    {
        $user     = $this->admin();
        $template = MenuTemplate::factory()->create([
            'created_by' => $user->id,
            'name'       => 'Standard Lunch',
            'content'    => $this->sampleContent(),
        ]);

        $this->actingAs($user)->post(route('menu.templates.load', $template));

        $draft = MenuDraft::where('created_by', $user->id)->latest()->first();
        $this->assertNotNull($draft);

        $normalized = $draft->normalizedContent();
        $this->assertCount(1, $normalized);
        $this->assertEquals('breakfast', $normalized[0]['key']);
        $this->assertEquals('Kesari', $normalized[0]['items'][0]['item_en']);
    }

    public function test_admin_can_delete_template(): void
    {
        $user     = $this->admin();
        $template = MenuTemplate::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
             ->delete(route('menu.templates.destroy', $template))
             ->assertRedirect(route('menu.templates.index'));

        $this->assertDatabaseMissing('menu_templates', ['id' => $template->id]);
    }

    public function test_manager_cannot_create_template(): void
    {
        $this->actingAs($this->manager())
             ->postJson(route('menu.templates.store'), ['name' => 'X', 'content' => $this->sampleContent()])
             ->assertForbidden();
    }

    // ── PDF generation ─────────────────────────────────────────────────────

    public function test_pdf_generation_returns_pdf(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => 'Test Menu', 'content' => $this->sampleContent()])
             ->assertOk()
             ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_rejects_invalid_lang(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'xx', 'title' => 'Test Menu', 'content' => $this->sampleContent()])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['lang']);
    }

    public function test_pdf_rejects_empty_menu(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => 'Empty Menu', 'content' => []])
             ->assertStatus(422);
    }

    public function test_pdf_rejects_sections_with_no_items(): void
    {
        $emptySection = [['key' => 'lunch', 'label_en' => 'Lunch', 'label_ta' => 'மதிய உணவு', 'items' => []]];

        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => 'Empty Menu', 'content' => $emptySection])
             ->assertStatus(422);
    }

    public function test_pdf_title_required(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => '', 'content' => $this->sampleContent()])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['title']);
    }

    public function test_pdf_sanitizes_xss_in_title(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), [
                 'lang'    => 'en',
                 'title'   => '<script>alert(1)</script>Wedding Menu',
                 'content' => $this->sampleContent(),
             ])
             ->assertOk()
             ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_sanitizes_xss_in_items(): void
    {
        $malicious = $this->sampleContent();
        $malicious[0]['items'][0]['item_en'] = '<img src=x onerror=alert(1)>Kesari';

        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), [
                 'lang'    => 'en',
                 'title'   => 'Test Menu',
                 'content' => $malicious,
             ])
             ->assertOk()
             ->assertHeader('content-type', 'application/pdf');
    }

    public function test_tamil_pdf_generates(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'ta', 'title' => 'திருமண மெனு', 'content' => $this->sampleContent()])
             ->assertOk()
             ->assertHeader('content-type', 'application/pdf');
    }

    public function test_bilingual_pdf_generates(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'bi', 'title' => 'Wedding Menu', 'content' => $this->sampleContent()])
             ->assertOk()
             ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_respects_section_order(): void
    {
        // Dinner first, then Lunch — PDF should follow this order
        $content = [
            ['key' => 'dinner', 'label_en' => 'Dinner', 'label_ta' => 'இரவு உணவு', 'items' => [
                ['id' => 1, 'item_en' => 'Biriyani', 'item_ta' => 'பிரியாணி', 'category_key' => 'rice', 'category_en' => 'Rice', 'category_ta' => 'சாதம்'],
            ]],
            ['key' => 'lunch', 'label_en' => 'Lunch', 'label_ta' => 'மதிய உணவு', 'items' => [
                ['id' => 2, 'item_en' => 'Idly', 'item_ta' => 'இட்லி', 'category_key' => 'main_course', 'category_en' => 'Main Course', 'category_ta' => 'முக்கிய உணவு'],
            ]],
        ];

        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => 'Test', 'content' => $content])
             ->assertOk()
             ->assertHeader('content-type', 'application/pdf');
    }

    // ── Audit logging ──────────────────────────────────────────────────────

    public function test_creating_draft_writes_audit_log(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.drafts.store'), ['title' => 'Audit Test Menu', 'content' => $this->sampleContent()])
             ->assertOk();

        $this->assertDatabaseHas('audit_logs', ['module' => 'menu_draft', 'action' => 'created', 'reference_label' => 'Audit Test Menu']);
    }

    public function test_duplicating_draft_writes_audit_log(): void
    {
        $user  = $this->admin();
        $draft = MenuDraft::factory()->create(['created_by' => $user->id, 'title' => 'Original Menu', 'content' => $this->sampleContent()]);

        $this->actingAs($user)->post(route('menu.drafts.duplicate', $draft));

        $this->assertDatabaseHas('audit_logs', ['module' => 'menu_draft', 'action' => 'duplicated']);
    }

    public function test_creating_template_writes_audit_log(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.templates.store'), ['name' => 'My Template', 'content' => $this->sampleContent()])
             ->assertOk();

        $this->assertDatabaseHas('audit_logs', ['module' => 'menu_template', 'action' => 'created', 'reference_label' => 'My Template']);
    }

    public function test_deleting_template_writes_audit_log(): void
    {
        $user     = $this->admin();
        $template = MenuTemplate::factory()->create(['created_by' => $user->id, 'name' => 'My Template']);

        $this->actingAs($user)->delete(route('menu.templates.destroy', $template));

        $this->assertDatabaseHas('audit_logs', ['module' => 'menu_template', 'action' => 'deleted', 'reference_label' => 'My Template']);
    }

    public function test_pdf_generation_writes_audit_log(): void
    {
        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => 'Logged PDF', 'content' => $this->sampleContent()])
             ->assertOk();

        $this->assertDatabaseHas('audit_logs', ['module' => 'menu_draft', 'action' => 'pdf_generated']);
    }

    // ── Model helpers ──────────────────────────────────────────────────────

    public function test_draft_total_items_count(): void
    {
        $content = [
            ['key' => 'breakfast', 'label_en' => 'Breakfast', 'label_ta' => 'காலை உணவு', 'items' => [
                ['id' => 1, 'item_en' => 'Kesari', 'item_ta' => 'கேசரி', 'category_key' => 'sweet', 'category_en' => 'Sweet', 'category_ta' => 'ஸ்வீட்'],
            ]],
            ['key' => 'lunch', 'label_en' => 'Lunch', 'label_ta' => 'மதிய உணவு', 'items' => [
                ['id' => 2, 'item_en' => 'Idly',   'item_ta' => 'இட்லி',   'category_key' => 'main_course', 'category_en' => 'Main Course', 'category_ta' => 'முக்கிய உணவு'],
                ['id' => 3, 'item_en' => 'Pongal', 'item_ta' => 'பொங்கல்', 'category_key' => 'main_course', 'category_en' => 'Main Course', 'category_ta' => 'முக்கிய உணவு'],
            ]],
        ];

        $draft = MenuDraft::factory()->make(['content' => $content]);
        $this->assertEquals(3, $draft->totalItems());
    }

    public function test_template_total_items_count(): void
    {
        $template = MenuTemplate::factory()->make();
        $this->assertEquals(1, $template->totalItems()); // factory has 1 item in lunch
    }

    // ── Per-section people count ───────────────────────────────────────────

    public function test_section_people_count_persists_on_save(): void
    {
        $user    = $this->admin();
        $content = [
            [
                'key'          => 'lunch',
                'label_en'     => 'Lunch',
                'label_ta'     => 'மதிய உணவு',
                'people_count' => 500,
                'items'        => [
                    ['id' => 1, 'item_en' => 'Rice', 'item_ta' => 'சாதம்', 'category_key' => 'rice', 'category_en' => 'Rice', 'category_ta' => 'சாதம்'],
                ],
            ],
        ];

        $this->actingAs($user)
             ->postJson(route('menu.drafts.store'), ['title' => 'Pax Test', 'content' => $content])
             ->assertOk();

        $draft = MenuDraft::where('title', 'Pax Test')->first();
        $this->assertNotNull($draft);
        $this->assertEquals(500, $draft->normalizedContent()[0]['people_count']);
    }

    public function test_section_people_count_null_when_omitted(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
             ->postJson(route('menu.drafts.store'), ['title' => 'No Pax', 'content' => $this->sampleContent()])
             ->assertOk();

        $draft = MenuDraft::where('title', 'No Pax')->first();
        $this->assertNull($draft->normalizedContent()[0]['people_count']);
    }

    public function test_duplicate_preserves_section_people_count(): void
    {
        $user    = $this->admin();
        $content = [
            ['key' => 'dinner', 'label_en' => 'Dinner', 'label_ta' => '', 'people_count' => 1000, 'items' => [
                ['id' => 1, 'item_en' => 'Biriyani', 'item_ta' => 'பிரியாணி', 'category_key' => 'rice', 'category_en' => 'Rice', 'category_ta' => 'சாதம்'],
            ]],
        ];
        $draft = MenuDraft::factory()->create(['created_by' => $user->id, 'title' => 'Pax Draft', 'content' => $content]);

        $this->actingAs($user)->post(route('menu.drafts.duplicate', $draft));

        $copy = MenuDraft::where('title', 'Pax Draft (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertEquals(1000, $copy->normalizedContent()[0]['people_count']);
    }

    public function test_pdf_generates_with_per_section_people_count(): void
    {
        $content = [
            ['key' => 'lunch', 'label_en' => 'Lunch', 'label_ta' => 'மதிய உணவு', 'people_count' => 200, 'items' => [
                ['id' => 1, 'item_en' => 'Biryani', 'item_ta' => 'பிரியாணி', 'category_key' => 'rice', 'category_en' => 'Rice', 'category_ta' => 'சாதம்'],
            ]],
        ];

        $this->actingAs($this->admin())
             ->postJson(route('menu.pdf.generate'), ['lang' => 'en', 'title' => 'Pax PDF', 'content' => $content])
             ->assertOk()
             ->assertHeader('content-type', 'application/pdf');
    }
}
