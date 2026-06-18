<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\MenuDraft;
use App\Models\MenuItem;
use App\Models\MenuTemplate;
use App\Services\AuditLogService;
use App\Services\MenuLetterheadService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MenuComposerController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    // ── Draft list ─────────────────────────────────────────────────────────

    public function index(Request $request): View|JsonResponse
    {
        $this->requireAdmin();

        $drafts = MenuDraft::where('created_by', auth()->id())
            ->latest('updated_at')
            ->paginate(12);

        // AJAX infinite-scroll: return rendered card HTML + pagination meta
        if ($request->expectsJson()) {
            $html = view('menu.composer._draft_cards', ['drafts' => $drafts])->render();
            return response()->json([
                'html'     => $html,
                'hasMore'  => $drafts->hasMorePages(),
                'nextPage' => $drafts->currentPage() + 1,
            ]);
        }

        $templates = MenuTemplate::orderBy('name')->get();

        return view('menu.composer.index', compact('drafts', 'templates'));
    }

    // ── New composer ───────────────────────────────────────────────────────

    public function create(): View
    {
        return view('menu.composer.create', $this->viewData(null));
    }

    // ── Edit draft ─────────────────────────────────────────────────────────

    public function edit(MenuDraft $draft): View
    {
        $this->requireDraftOwner($draft);

        return view('menu.composer.edit', array_merge(
            $this->viewData($draft),
            ['draft' => $draft]
        ));
    }

    // ── Save new draft (AJAX JSON) ─────────────────────────────────────────

    public function storeDraft(Request $request): JsonResponse
    {
        $data  = $this->validateDraftPayload($request);
        $draft = MenuDraft::create([
            'title'        => $data['title'],
            'venue'        => $data['venue'] ?? null,
            'event_date'   => $data['event_date'] ?? null,
            'people_count' => $data['people_count'] ?? null,
            'content'      => $this->sanitizeContent($data['content'] ?? []),
            'created_by'   => auth()->id(),
        ]);

        $this->audit->log('created', 'menu_draft', $draft->id, $draft->title);

        return response()->json([
            'id'       => $draft->id,
            'edit_url' => route('menu.drafts.edit', $draft),
        ]);
    }

    // ── Update draft (AJAX JSON) ───────────────────────────────────────────

    public function updateDraft(Request $request, MenuDraft $draft): JsonResponse
    {
        $this->requireDraftOwner($draft);
        $data = $this->validateDraftPayload($request);

        $draft->update([
            'title'        => $data['title'],
            'venue'        => $data['venue'] ?? null,
            'event_date'   => $data['event_date'] ?? null,
            'people_count' => $data['people_count'] ?? null,
            'content'      => $this->sanitizeContent($data['content'] ?? []),
        ]);

        $this->audit->log('updated', 'menu_draft', $draft->id, $draft->title);

        return response()->json(['ok' => true]);
    }

    // ── Duplicate draft ────────────────────────────────────────────────────

    public function duplicateDraft(MenuDraft $draft): RedirectResponse
    {
        $this->requireDraftOwner($draft);

        $baseTitle = $draft->title;

        // Strip any existing "(Copy N)" suffix to get clean base
        $baseTitle = preg_replace('/\s*\(Copy(?:\s+\d+)?\)\s*$/', '', $baseTitle);

        // Find highest existing copy number
        $existing = MenuDraft::where('created_by', auth()->id())
            ->where('title', 'like', $baseTitle . ' (Copy%')
            ->pluck('title');

        $maxN = 0;
        foreach ($existing as $t) {
            if (preg_match('/\(Copy(?:\s+(\d+))?\)$/', $t, $m)) {
                $maxN = max($maxN, isset($m[1]) ? (int) $m[1] : 1);
            }
        }

        $newTitle = $baseTitle . ($maxN === 0 ? ' (Copy)' : ' (Copy ' . ($maxN + 1) . ')');

        $copy = MenuDraft::create([
            'title'        => $newTitle,
            'venue'        => $draft->venue,
            'event_date'   => $draft->event_date?->format('Y-m-d'),
            'people_count' => $draft->people_count,
            'content'      => $draft->normalizedContent(),
            'created_by'   => auth()->id(),
        ]);

        $this->audit->log('duplicated', 'menu_draft', $copy->id, $copy->title);

        return redirect()->route('menu.drafts.edit', $copy)
            ->with('success', 'Draft duplicated as "' . $newTitle . '".');
    }

    // ── Delete draft ───────────────────────────────────────────────────────

    public function destroyDraft(MenuDraft $draft): RedirectResponse
    {
        $this->requireDraftOwner($draft);
        $label = $draft->title;
        $draft->delete();

        $this->audit->log('deleted', 'menu_draft', $draft->id, $label);

        return redirect()->route('menu.composer.index')
            ->with('success', '"' . $label . '" deleted.');
    }

    // ── Templates index ────────────────────────────────────────────────────

    public function templatesIndex(): View
    {
        $templates = MenuTemplate::orderBy('name')->get();

        return view('menu.templates.index', compact('templates'));
    }

    // ── Store template ─────────────────────────────────────────────────────

    public function storeTemplate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content'     => ['required', 'array'],
        ]);

        $template = MenuTemplate::create([
            'name'        => trim($data['name']),
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'content'     => $this->sanitizeContent($data['content']),
            'created_by'  => auth()->id(),
        ]);

        $this->audit->log('created', 'menu_template', $template->id, $template->name);

        return response()->json([
            'id'   => $template->id,
            'name' => $template->name,
        ]);
    }

    // ── Update template ────────────────────────────────────────────────────

    public function updateTemplate(Request $request, MenuTemplate $template): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content'     => ['required', 'array'],
        ]);

        $template->update([
            'name'        => trim($data['name']),
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'content'     => $this->sanitizeContent($data['content']),
        ]);

        $this->audit->log('updated', 'menu_template', $template->id, $template->name);

        return response()->json(['ok' => true]);
    }

    // ── Delete template ────────────────────────────────────────────────────

    public function destroyTemplate(MenuTemplate $template): RedirectResponse
    {
        $label = $template->name;
        $id    = $template->id;
        $template->delete();

        $this->audit->log('deleted', 'menu_template', $id, $label);

        return redirect()->route('menu.templates.index')
            ->with('success', '"' . $label . '" template deleted.');
    }

    // ── Load template → redirect to new draft ─────────────────────────────

    public function loadTemplate(Request $request, MenuTemplate $template): RedirectResponse
    {
        $draft = MenuDraft::create([
            'title'        => $template->name . ' — ' . now()->format('d M Y'),
            'venue'        => null,
            'event_date'   => null,
            'people_count' => null,
            'content'      => $template->normalizedContent(),
            'created_by'   => auth()->id(),
        ]);

        $this->audit->log('created', 'menu_draft', $draft->id, $draft->title . ' [from template]');

        return redirect()->route('menu.drafts.edit', $draft)
            ->with('success', 'Draft created from template "' . $template->name . '".');
    }

    // ── Generate PDF ───────────────────────────────────────────────────────

    public function generatePdf(Request $request): Response
    {
        $request->validate([
            'lang'         => ['required', 'in:en,ta,bi'],
            'title'        => ['required', 'string', 'max:255'],
            'venue'        => ['nullable', 'string', 'max:255'],
            'event_date'   => ['nullable', 'date'],
            'people_count' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'content'      => ['required', 'array'],
            'letterhead'   => ['nullable', 'boolean'],
        ]);

        $lang    = $request->input('lang');
        $content = $this->sanitizeContent($request->input('content', []));

        $totalItems = array_sum(array_map(fn($s) => count($s['items'] ?? []), $content));
        abort_if($totalItems === 0, 422, 'Menu has no items.');

        $title = trim(strip_tags($request->input('title')));
        $venue = $request->input('venue') ? trim(strip_tags($request->input('venue'))) : null;

        Log::info('[MenuPDF] entered', ['lang' => $lang, 'sections' => count($content), 'items' => $totalItems]);

        $letterheadBg = null;
        if ($request->boolean('letterhead', true)) {
            $letterheadBg = app(MenuLetterheadService::class)->jpegPath();
        }

        $viewData = [
            'lang'         => $lang,
            'title'        => $title,
            'venue'        => $venue,
            'event_date'   => $request->input('event_date'),
            'people_count' => $request->integer('people_count') ?: null,
            'content'      => $content,
            'categories'   => config('menu_categories.items', []),
            'fontPath'     => $this->tamilFontPath(),
            'letterheadBg' => $letterheadBg,
        ];

        $html     = view('menu.pdf.layout', $viewData)->render();
        $output   = $this->renderPdfViaChrome($html);
        $filename = 'menu-' . now()->format('Ymd-His') . '-' . $lang . '.pdf';

        Log::info('[MenuPDF] generated', ['filename' => $filename, 'bytes' => strlen($output), 'bg' => $letterheadBg ? 'letterhead' : 'standard']);

        $suffix = $letterheadBg ? 'letterhead' : 'standard';
        $this->audit->log('pdf_generated', 'menu_draft', null, $title . ' (' . strtoupper($lang) . ', ' . $suffix . ')');

        return response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Render HTML to PDF using headless Chrome.
     *
     * Chrome has full OpenType shaping (HarfBuzz) — Tamil renders correctly.
     * dompdf has no shaping engine and cannot render Tamil script.
     *
     * @throws \RuntimeException on Chrome failure
     */
    private function renderPdfViaChrome(string $html): string
    {
        $chrome = '/usr/bin/google-chrome';
        if (! is_executable($chrome)) {
            throw new \RuntimeException('Headless Chrome not found at ' . $chrome);
        }

        $htmlFile = tempnam(sys_get_temp_dir(), 'menupdf_') . '.html';
        $pdfFile  = sys_get_temp_dir() . '/' . basename($htmlFile, '.html') . '.pdf';

        try {
            file_put_contents($htmlFile, $html);

            $cmd = implode(' ', [
                escapeshellcmd($chrome),
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-extensions',
                '--run-all-compositor-stages-before-draw',
                '--print-to-pdf-no-header',
                '--allow-file-access-from-files',
                '--print-to-pdf=' . escapeshellarg($pdfFile),
                '"file://' . $htmlFile . '"',
                '2>&1',
            ]);

            exec($cmd, $output, $exitCode);

            if (! file_exists($pdfFile) || filesize($pdfFile) < 100) {
                throw new \RuntimeException(
                    'Chrome PDF failed (exit ' . $exitCode . '): ' . implode("\n", $output)
                );
            }

            return file_get_contents($pdfFile);

        } finally {
            @unlink($htmlFile);
            @unlink($pdfFile);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function requireAdmin(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Admin access required.');
        }
    }

    private function requireDraftOwner(MenuDraft $draft): void
    {
        $this->requireAdmin();
        if ($draft->created_by !== auth()->id()) {
            abort(403, 'You do not own this draft.');
        }
    }

    private function viewData(?MenuDraft $draft = null): array
    {
        return [
            'menuItems'      => MenuItem::forComposer(),
            'categoryKeys'   => MenuItem::categoryKeys(),
            'categories'     => config('menu_categories.items', []),
            'mealSections'   => config('menu_categories.meal_sections', []),
            'brand'          => config('menu_categories.brand_name', 'AKSHATHAY'),
            'templates'      => MenuTemplate::orderBy('name')->get(),
            'initialContent' => $draft ? $draft->normalizedContent() : [],
            'initialMeta'    => $draft
                ? ['title' => $draft->title, 'venue' => $draft->venue, 'event_date' => $draft->event_date?->format('Y-m-d'), 'people_count' => $draft->people_count]
                : ['title' => '', 'venue' => '', 'event_date' => '', 'people_count' => ''],
        ];
    }

    private function validateDraftPayload(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'venue'        => ['nullable', 'string', 'max:255'],
            'event_date'   => ['nullable', 'date'],
            'people_count' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'content'      => ['nullable', 'array'],
        ]);
    }

    /**
     * Sanitize content in the new array-of-sections format.
     * Also accepts old keyed-object format and normalizes it first.
     * Strips HTML from all user-supplied fields. Filters items missing item_en.
     */
    private function sanitizeContent(array $raw): array
    {
        // Auto-migrate old format → new
        $sections = MenuDraft::normalizeContentArray($raw);

        $validKeys = array_keys(config('menu_categories.meal_sections', []));

        return array_values(array_map(function ($section) use ($validKeys) {
            $key      = substr(trim(strip_tags($section['key']      ?? 'custom')), 0, 50);
            $labelEn  = substr(trim(strip_tags($section['label_en'] ?? '')), 0, 100);
            $labelTa  = substr(trim(strip_tags($section['label_ta'] ?? '')), 0, 100);
            $items    = $section['items'] ?? [];

            // Reject unrecognised non-custom keys
            if (! in_array($key, $validKeys) && ! str_starts_with($key, 'custom')) {
                $key = 'custom';
            }

            return [
                'key'          => $key,
                'label_en'     => $labelEn ?: 'Section',
                'label_ta'     => $labelTa,
                'people_count' => isset($section['people_count']) && $section['people_count'] !== ''
                    ? max(1, (int) $section['people_count'])
                    : null,
                'items'    => array_values(array_map(
                    fn($item) => [
                        'id'           => (int)  ($item['id']           ?? 0),
                        'item_en'      => substr(trim(strip_tags($item['item_en']      ?? '')), 0, 200),
                        'item_ta'      => substr(trim(strip_tags($item['item_ta']      ?? '')), 0, 200),
                        'category_key' => substr(trim(strip_tags($item['category_key'] ?? '')), 0, 50),
                        'category_en'  => substr(trim(strip_tags($item['category_en']  ?? '')), 0, 100),
                        'category_ta'  => substr(trim(strip_tags($item['category_ta']  ?? '')), 0, 100),
                    ],
                    array_filter($items, fn($i) => ! empty($i['item_en']))
                )),
            ];
        }, array_filter($sections, fn($s) => isset($s['key']))));
    }

    private function tamilFontPath(): ?string
    {
        $path = storage_path('fonts/NotoSansTamil-Regular.ttf');
        return file_exists($path) ? $path : null;
    }
}
