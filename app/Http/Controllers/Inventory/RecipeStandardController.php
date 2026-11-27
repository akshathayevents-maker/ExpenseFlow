<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Recipe;
use App\Services\Inventory\RecipeStandardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin: turn recorded usage into versioned per-person standards. */
class RecipeStandardController extends Controller
{
    public function __construct(private RecipeStandardService $standards) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('inventory.standards.index', [
            'preparations' => $this->standards->preparations(),
            'versions' => Recipe::whereHas('ingredients', fn ($q) => $q->whereNotNull('quantity_per_guest'))
                ->orderBy('name')->orderByDesc('version')->get(['id', 'name', 'version', 'superseded_at', 'created_at']),
        ]);
    }

    public function show(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        $name = (string) $request->validate(['name' => ['required', 'string', 'max:150']])['name'];

        $h = $this->standards->history($name);

        return view('inventory.standards.show', [
            'h' => $h,
            'versions' => Recipe::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
                ->whereHas('ingredients', fn ($q) => $q->whereNotNull('quantity_per_guest'))
                ->with('createdBy:id,name')->orderByDesc('version')->get(['id', 'name', 'version', 'superseded_at', 'created_at', 'created_by', 'description']),
            'drift' => $h['current'] ? $this->standards->drift($name, $h['current']) : null,
            'items' => InventoryItem::active()->orderBy('name')->get(['id', 'name', 'unit']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'lines' => ['required', 'array', 'max:100'],
            'lines.*' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'extra_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'extra_per_guest' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $lines = $data['lines'];
        if (! empty($data['extra_item_id']) && ! empty($data['extra_per_guest'])) {
            $lines[$data['extra_item_id']] = $data['extra_per_guest'];
        }
        $recipe = $this->standards->saveStandard($data['name'], $lines, $request->user(), $data['notes'] ?? null);

        return redirect()->route('inventory.standards.show', ['name' => $data['name']])
            ->with('success', "Saved {$recipe->name} version {$recipe->version}. Earlier requests are unchanged.");
    }
}
