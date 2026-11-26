<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Services\AuditLogService;
use App\Services\Inventory\ItemCatalogueService;
use App\Services\Inventory\ItemSearchService;
use App\Services\Inventory\UnitConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "+ Add as new item" from the item picker, without leaving the form.
 * Admin-created items are ready at once; items created by other staff are usable at once
 * but flagged needs_review so an admin can check them in the catalogue.
 */
class ItemQuickAddController extends Controller
{
    public function __construct(
        private ItemSearchService $search,
        private ItemCatalogueService $catalogue,
        private UnitConversionService $units,
        private AuditLogService $audit,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->is_active, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'name_ta' => ['nullable', 'string', 'max:200'],
            'inventory_category_id' => ['required', Rule::exists('inventory_categories', 'id')->where('is_active', true)],
            'unit' => ['required', Rule::in(array_keys(config('inventory.units')))],
            'confirm' => ['nullable', 'boolean'],
        ]);
        $name = trim(preg_replace('/\s+/u', ' ', $data['name']));
        if ($name === mb_strtolower($name)) {   // typed in a hurry: "boost" -> "Boost"
            $name = mb_convert_case($name, MB_CASE_TITLE);
        }

        // same name (or alias) already exists: hand that item back instead of creating a duplicate
        if ($existing = $this->catalogue->itemNamed($name)) {
            $existing = InventoryItem::find($existing->id);
            if ($existing->status !== 'active') {
                return response()->json(['message' => "“{$existing->name}” exists but is inactive. Ask an admin to re-activate it."], 422);
            }

            return response()->json(['status' => 'exists', 'item' => $this->payload($existing)]);
        }

        if (! $request->boolean('confirm')) {
            $similar = collect($this->search->similarTo($name))
                ->merge(! empty($data['name_ta']) ? $this->search->similarTo($data['name_ta']) : [])
                ->where('status', 'active')->unique('id')->take(5)->values();
            if ($similar->isNotEmpty()) {
                return response()->json(['status' => 'similar', 'similar' => $similar->map(fn ($r) => [
                    'id' => $r['id'], 'name' => $r['name'], 'name_ta' => $r['name_ta'] ?? null, 'unit' => $r['unit'], 'category' => $r['category'] ?? null,
                ])], 409);
            }
        }

        $item = InventoryItem::create([
            'name' => $name,
            'name_ta' => isset($data['name_ta']) ? trim($data['name_ta']) ?: null : null,
            'inventory_category_id' => $data['inventory_category_id'],
            'unit' => $data['unit'],
            'minimum_stock' => 0,
            'status' => 'active',
            'needs_review' => ! $user->isAdmin(),
            'created_by' => $user->id,
        ]);
        $this->audit->log('created', 'inventory_item', $item->id, $item->name, [], ['via' => 'quick_add', 'needs_review' => $item->needs_review]);

        return response()->json(['status' => 'created', 'item' => $this->payload($item)], 201);
    }

    /** Same shape as the purchase / material-request form catalogue, so forms can use it directly. */
    private function payload(InventoryItem $i): array
    {
        $i->loadMissing('category', 'unitConversions');

        return [
            'id' => $i->id, 'name' => $i->name, 'name_ta' => $i->name_ta, 'unit' => $i->unit,
            'units' => $this->units->unitsFor($i), 'stock' => round((float) $i->current_stock, 3), 'rate' => null,
            'category' => $i->category?->name, 'status' => $i->status, 'needs_review' => (bool) $i->needs_review,
        ];
    }
}
