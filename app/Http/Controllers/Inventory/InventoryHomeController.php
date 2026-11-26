<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Role-based "Store" home: kitchen staff, storekeeper and admin each see their own tasks. */
class InventoryHomeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = now('Asia/Kolkata')->toDateString();

        $kitchen = [
            'open' => MaterialRequest::where('requested_by', $user->id)->whereIn('status', ['draft', 'submitted', 'approved', 'issued'])->count(),
            'waiting' => MaterialRequest::where('requested_by', $user->id)->where('status', 'submitted')->count(),
            'usage' => MaterialRequest::where('requested_by', $user->id)->where('status', 'issued')->count(),
            'recent' => MaterialRequest::with('mealEntry.client', 'hallBooking')->where('requested_by', $user->id)->latest('id')->take(5)->get(),
        ];

        $store = null;
        if ($user->isStorekeeper()) {
            $store = [
                'handover' => MaterialRequest::where('status', 'approved')->count(),
                'issuedToday' => MaterialRequest::whereDate('handed_over_at', $today)->count(),
                'out' => MaterialRequest::where('status', 'issued')->count(),
                'receive' => Purchase::whereIn('status', ['approved', 'partially_received'])->count(),
                'queue' => MaterialRequest::with('requester', 'mealEntry.client', 'hallBooking')->where('status', 'approved')
                    ->orderBy('required_date')->orderBy('required_time')->take(8)->get(),
            ];
        }

        $admin = null;
        if ($user->isAdmin()) {
            $admin = [
                'purchases' => Purchase::where('status', 'submitted')->count(),
                'materials' => MaterialRequest::where('status', 'submitted')->count(),
                'alerts' => InventoryItem::active()->critical()->where('minimum_stock', '>', 0)->count(),   // only items with a reorder level
                'review' => MaterialRequest::where('reconciliation_status', 'pending_review')->count(),
                'unreconciled' => MaterialRequest::where('status', 'issued')->whereDate('required_date', '<', $today)->count(),
            ];
        }

        // stock snapshot + what is needed soon (store-side roles)
        $snapshot = null;
        $upcoming = collect();
        if ($user->isAdmin() || $user->isManager() || $user->isStorekeeper()) {
            $active = InventoryItem::active();
            $snapshot = [
                'in' => (clone $active)->where('current_stock', '>', 0)->count(),
                'low' => (clone $active)->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'minimum_stock')->count(),
                'out' => (clone $active)->where('current_stock', '<=', 0)->count(),
                'incoming' => PurchaseItem::whereHas('purchase', fn ($q) => $q->whereIn('status', ['approved', 'partially_received']))
                    ->whereRaw('base_quantity - received_base_quantity - closed_base_quantity > 0')->distinct()->count('inventory_item_id'),
                'value' => $user->isAdmin() || $user->isManager()
                    ? (float) InventoryItem::active()->where('current_stock', '>', 0)->selectRaw('SUM(current_stock * COALESCE(average_cost, 0)) as v')->value('v') : null,
            ];
            $upcoming = MaterialRequest::with('requester', 'mealEntry.client', 'hallBooking')
                ->whereIn('status', ['submitted', 'approved', 'issued'])
                ->whereBetween('required_date', [$today, now('Asia/Kolkata')->addDay()->toDateString()])
                ->orderBy('required_date')->orderBy('required_time')->take(8)->get();
        }

        return view('inventory.home', compact('kitchen', 'store', 'admin', 'snapshot', 'upcoming'));
    }

    /** Central store picture: physical, awaiting handover, available, incoming, pending. */
    public function stock(Request $request): View
    {
        abort_unless($request->user()->isAdmin() || $request->user()->isManager() || $request->user()->isStorekeeper(), 403);

        $awaiting = MaterialRequestItem::whereHas('request', fn ($q) => $q->where('status', 'approved'))
            ->where('line_status', '!=', 'rejected')
            ->selectRaw('inventory_item_id, SUM(approved_base_quantity) as q')->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id');
        $pending = MaterialRequestItem::whereHas('request', fn ($q) => $q->where('status', 'submitted'))
            ->selectRaw('inventory_item_id, SUM(requested_base_quantity) as q')->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id');
        $incoming = PurchaseItem::whereHas('purchase', fn ($q) => $q->whereIn('status', ['approved', 'partially_received']))
            ->selectRaw('inventory_item_id, SUM(base_quantity - received_base_quantity - closed_base_quantity) as q')->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id');

        $search = trim((string) $request->input('q'));
        $show = in_array($request->input('show'), ['in', 'low', 'out', 'incoming'], true) ? $request->input('show') : '';
        $cat = $request->integer('cat') ?: null;

        // Every active item is rendered; filters are applied as `hidden` so the page can re-filter instantly
        // in the browser while links such as ?show=low keep working without JavaScript.
        $items = InventoryItem::with('category')->active()->orderBy('name')->get()
            ->map(function ($i) use ($awaiting, $pending, $incoming) {
                $i->awaiting = (float) ($awaiting[$i->id] ?? 0);
                $i->physical = round($i->current_stock + $i->awaiting, 3);
                $i->pending = (float) ($pending[$i->id] ?? 0);
                $i->incoming = (float) ($incoming[$i->id] ?? 0);
                $i->state = $i->isOutOfStock() ? 'out' : ($i->current_stock <= $i->minimum_stock ? 'low' : 'ok');
                $i->haystack = mb_strtolower(trim($i->name.' '.$i->name_ta.' '.$i->sku.' '.$i->category?->name));

                $i->active = $i->current_stock > 0 || $i->incoming > 0 || $i->pending > 0 || $i->awaiting > 0;

                return $i;
            })
            // items with stock or movement first, then alphabetical — the long tail of never-stocked items goes last
            ->sortBy([fn ($a, $b) => $b->active <=> $a->active, fn ($a, $b) => strcasecmp($a->name, $b->name)])->values();

        $match = fn ($i) => ($search === '' || str_contains($i->haystack, mb_strtolower($search)))
            && (! $cat || $i->inventory_category_id === $cat)
            && match ($show) {
                'low' => $i->state !== 'ok', 'out' => $i->state === 'out', 'in' => $i->current_stock > 0, 'incoming' => $i->incoming > 0, default => true,
            };
        $shown = $items->filter($match)->pluck('id')->flip();

        $counts = [
            'all' => $items->count(),
            'low' => $items->where('state', '!=', 'ok')->count(),
            'out' => $items->where('state', 'out')->count(),
            'in' => $items->where('current_stock', '>', 0)->count(),
            'incoming' => $items->where('incoming', '>', 0)->count(),
        ];
        $categories = $items->pluck('category')->filter()->unique('id')->sortBy('name')->values();

        return view('inventory.stock', compact('items', 'shown', 'search', 'show', 'cat', 'counts', 'categories'));
    }
}
