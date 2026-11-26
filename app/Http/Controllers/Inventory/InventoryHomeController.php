<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Role-based "Store" home: kitchen staff, storekeeper and admin each see their own tasks. */
class InventoryHomeController extends Controller
{
    /**
     * Store home — an operational inbox. Counts are always live (no caching: they drive approvals and
     * stock decisions). Status counts are one grouped query per table; the stock picture is one
     * aggregate query; lists are small (≤ 6 rows) and eager-loaded.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = now('Asia/Kolkata')->toDateString();
        $tomorrow = now('Asia/Kolkata')->addDay()->toDateString();
        $isAdmin = $user->isAdmin();
        $isStorekeeper = $user->isStorekeeper();
        $storeSide = $isAdmin || $user->isManager() || $isStorekeeper;
        $withLinks = ['requester:id,name', 'mealEntry.client', 'hallBooking'];

        // my own requests: waiting / issued (usage to record) in one query
        $mine = MaterialRequest::where('requested_by', $user->id)
            ->selectRaw("SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS waiting, SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) AS usage_due")
            ->first();
        $kitchen = [
            'waiting' => (int) $mine?->waiting,
            'usage' => (int) $mine?->usage_due,
            'recent' => MaterialRequest::with('mealEntry.client', 'hallBooking')->where('requested_by', $user->id)->latest('id')->take(5)->get(),
        ];

        $req = $pur = [];
        $admin = $store = $snapshot = null;
        $critical = $arriving = $activity = $upcoming = collect();
        $incomingFor = collect();

        if ($storeSide) {
            $req = MaterialRequest::selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status')->map(fn ($c) => (int) $c);
            $pur = Purchase::selectRaw('status, COUNT(*) AS c')->whereIn('status', ['submitted', 'approved', 'partially_received'])
                ->groupBy('status')->pluck('c', 'status')->map(fn ($c) => (int) $c);

            // one pass over inventory_items: in / low / out / tracked-critical / value
            $agg = InventoryItem::active()->selectRaw('
                COUNT(*) AS total,
                SUM(CASE WHEN current_stock > 0 THEN 1 ELSE 0 END) AS in_stock,
                SUM(CASE WHEN current_stock > 0 AND current_stock <= minimum_stock THEN 1 ELSE 0 END) AS low,
                SUM(CASE WHEN current_stock <= 0 THEN 1 ELSE 0 END) AS out_all,
                SUM(CASE WHEN current_stock <= 0 AND minimum_stock > 0 THEN 1 ELSE 0 END) AS out_tracked,
                SUM(CASE WHEN current_stock > 0 THEN current_stock * COALESCE(average_cost, 0) ELSE 0 END) AS value')->first();
            $snapshot = [
                'total' => (int) $agg->total,
                'in' => (int) $agg->in_stock,
                'low' => (int) $agg->low,
                'out' => (int) $agg->out_all,
                'outTracked' => (int) $agg->out_tracked,
                'incoming' => PurchaseItem::whereHas('purchase', fn ($q) => $q->whereIn('status', ['approved', 'partially_received']))
                    ->whereRaw('base_quantity - received_base_quantity - closed_base_quantity > 0')->distinct()->count('inventory_item_id'),
                'value' => $isAdmin || $user->isManager() ? (float) $agg->value : null,
            ];

            // most critical items that have a reorder level: out first, then lowest % of minimum
            $critical = InventoryItem::active()->where('minimum_stock', '>', 0)->whereColumn('current_stock', '<=', 'minimum_stock')
                ->orderByRaw('CASE WHEN current_stock <= 0 THEN 0 ELSE 1 END')->orderByRaw('current_stock / minimum_stock')->orderBy('name')
                ->take(6)->get(['id', 'name', 'name_ta', 'unit', 'current_stock', 'minimum_stock']);
            if ($critical->isNotEmpty()) {
                $incomingFor = PurchaseItem::whereIn('inventory_item_id', $critical->pluck('id'))
                    ->whereHas('purchase', fn ($q) => $q->whereIn('status', ['approved', 'partially_received']))
                    ->selectRaw('inventory_item_id, SUM(base_quantity - received_base_quantity - closed_base_quantity) AS q')
                    ->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id')->map(fn ($q) => (float) $q);
            }

            $upcoming = MaterialRequest::with($withLinks)->whereIn('status', ['submitted', 'approved', 'issued'])
                ->whereBetween('required_date', [$today, $tomorrow])
                ->orderBy('required_date')->orderBy('required_time')->take(6)->get();

            // only people who may open a purchase see the arriving list (PurchasePolicy::view)
            if ($isAdmin || $isStorekeeper) {
                $arriving = Purchase::with('vendor:id,name')->withCount('items')->whereIn('status', ['approved', 'partially_received'])
                    ->orderBy('purchase_date')->take(5)->get();
            }

            $activity = InventoryTransaction::with('item:id,name,unit', 'creator:id,name')->latest('id')->take(8)
                ->get(['id', 'inventory_item_id', 'type', 'quantity', 'balance_before', 'balance_after', 'reference_label', 'created_by', 'created_at']);
        }

        if ($isAdmin) {
            $admin = [
                'purchases' => $pur['submitted'] ?? 0,
                'materials' => $req['submitted'] ?? 0,
                'alerts' => $snapshot['low'] + $snapshot['outTracked'],   // only items with a reorder level
                'review' => MaterialRequest::where('reconciliation_status', 'pending_review')->count(),
                'unreconciled' => MaterialRequest::where('status', 'issued')->whereDate('required_date', '<', $today)->count(),
                'queue' => MaterialRequest::with($withLinks)->where('status', 'submitted')
                    ->orderBy('required_date')->orderBy('required_time')->take(6)->get(),
            ];
        }
        if ($isStorekeeper) {
            $store = [
                'handover' => $req['approved'] ?? 0,
                'out' => $req['issued'] ?? 0,
                'receive' => ($pur['approved'] ?? 0) + ($pur['partially_received'] ?? 0),
                'queue' => MaterialRequest::with($withLinks)->where('status', 'approved')
                    ->orderBy('required_date')->orderBy('required_time')->take(6)->get(),
            ];
        }

        return view('inventory.home', compact('kitchen', 'store', 'admin', 'snapshot', 'upcoming', 'critical', 'incomingFor', 'arriving', 'activity')
            + ['openRequests' => (int) collect($req)->only(['submitted', 'approved', 'issued'])->sum()]);
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
