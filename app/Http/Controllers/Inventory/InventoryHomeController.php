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
                'alerts' => InventoryItem::active()->critical()->count(),
                'review' => MaterialRequest::where('reconciliation_status', 'pending_review')->count(),
                'unreconciled' => MaterialRequest::where('status', 'issued')->whereDate('required_date', '<', $today)->count(),
            ];
        }

        return view('inventory.home', compact('kitchen', 'store', 'admin'));
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
            ->selectRaw('inventory_item_id, SUM(base_quantity - received_base_quantity) as q')->groupBy('inventory_item_id')->pluck('q', 'inventory_item_id');

        $search = trim((string) $request->input('q'));
        $items = InventoryItem::with('category')->active()
            ->when($search !== '', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%']))
            ->when($request->input('show') === 'low', fn ($q) => $q->critical())
            ->orderBy('name')->get()
            ->map(function ($i) use ($awaiting, $pending, $incoming) {
                $i->awaiting = (float) ($awaiting[$i->id] ?? 0);
                $i->physical = round($i->current_stock + $i->awaiting, 3);
                $i->pending = (float) ($pending[$i->id] ?? 0);
                $i->incoming = (float) ($incoming[$i->id] ?? 0);

                return $i;
            });

        return view('inventory.stock', compact('items', 'search'));
    }
}
