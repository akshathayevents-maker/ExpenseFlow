<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchasePlanRequest;
use App\Models\InventoryItem;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanItem;
use App\Services\AuditLogService;
use App\Services\PurchasePlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchasePlanController extends Controller
{
    public function __construct(
        private PurchasePlanningService $planningService,
        private AuditLogService $audit
    ) {}

    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
        $status = in_array($request->query('status'), ['submitted', 'draft', 'rejected', 'approved', 'ordered', 'completed', 'cancelled'], true) ? $request->query('status') : null;   // unknown values are ignored (old links)
        $search = trim((string) ($f['q'] ?? ''));

        // estimated total + priority counts computed in SQL, no item models loaded for the list
        $plans = PurchasePlan::select('purchase_plans.*')->with('creator:id,name')
            ->withCount(['items', 'items as urgent_count' => fn ($q) => $q->whereIn('priority', ['urgent', 'high'])])
            ->addSelect(['est_total' => PurchasePlanItem::selectRaw('COALESCE(SUM(COALESCE(estimated_unit_cost, 0) * suggested_quantity), 0)')
                ->whereColumn('purchase_plan_id', 'purchase_plans.id')])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($search).'%']))
            ->when($f['from'] ?? null, fn ($q, $d) => $q->whereDate('planned_date', '>=', $d))
            ->when($f['to'] ?? null, fn ($q, $d) => $q->whereDate('planned_date', '<=', $d))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $byStatus = PurchasePlan::selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status')->map(fn ($c) => (int) $c);
        $stats = collect(['submitted', 'draft', 'rejected', 'approved', 'ordered', 'completed', 'cancelled'])->mapWithKeys(fn ($k) => [$k => $byStatus[$k] ?? 0])->all();
        $stats['total'] = $byStatus->sum();

        // what needs buying right now (items with a reorder level at or below it)
        $lowQuery = InventoryItem::active()->where('minimum_stock', '>', 0)->whereColumn('current_stock', '<=', 'minimum_stock');
        $lowCount = (clone $lowQuery)->count();
        $lowItems = $lowQuery->orderByRaw('CASE WHEN current_stock <= 0 THEN 0 ELSE 1 END')->orderByRaw('current_stock / minimum_stock')
            ->take(5)->get(['id', 'name', 'unit', 'current_stock', 'minimum_stock']);
        $filters = array_filter(['q' => $search, 'from' => $f['from'] ?? null, 'to' => $f['to'] ?? null]);

        return view('admin.purchase-plans.index', compact('plans', 'stats', 'status', 'lowCount', 'lowItems', 'filters', 'search'));
    }

    public function suggestions(): View
    {
        $suggestions = $this->planningService->getSuggestions();
        $unconfigured = $this->planningService->unconfiguredEmptyCount();
        $categories = $suggestions->pluck('category')->filter()->unique('id')->sortBy('name')->values();

        return view('admin.purchase-plans.suggestions', compact('suggestions', 'unconfigured', 'categories'));
    }

    public function create(): View
    {
        // one plan form for everyone (the staff workflow page); admins land here from suggestions / the store dashboard
        return view('inventory.plans.form', ['plan' => null, 'rows' => \App\Http\Controllers\Inventory\PurchasePlanController::formRows(null)]);
    }

    public function store(PurchasePlanRequest $request): RedirectResponse
    {
        $plan = $this->planningService->createFromSuggestions($request->validated(), auth()->user());
        $this->audit->log('created', 'purchase_plan', $plan->id, $plan->title);

        return redirect()->route('admin.purchase-plans.show', $plan)->with('success', 'Purchase plan created.');
    }

    public function show(PurchasePlan $purchasePlan): View
    {
        $purchasePlan->load(['items.inventoryItem.category', 'creator', 'approver']);

        return view('admin.purchase-plans.show', compact('purchasePlan'));
    }

    public function approve(PurchasePlan $purchasePlan): RedirectResponse
    {
        if (! $purchasePlan->isDraft()) {
            return back()->with('error', 'Only draft plans can be approved.');
        }

        $this->planningService->approve($purchasePlan, auth()->user());
        $this->audit->log('approved', 'purchase_plan', $purchasePlan->id, $purchasePlan->title);

        return back()->with('success', 'Plan approved.');
    }

    public function updateStatus(Request $request, PurchasePlan $purchasePlan): RedirectResponse
    {
        $request->validate(['status' => 'required|in:ordered,completed,cancelled']);
        $allowed = [
            'ordered' => ['approved'],
            'completed' => ['ordered'],
            'cancelled' => ['draft', 'approved', 'ordered'],
        ][$request->status];
        if (! in_array($purchasePlan->status, $allowed, true)) {
            return back()->with('error', 'A '.$purchasePlan->status.' plan cannot be marked '.$request->status.'.');
        }
        $purchasePlan->update(['status' => $request->status]);
        $this->audit->log($request->status, 'purchase_plan', $purchasePlan->id, $purchasePlan->title);

        return back()->with('success', 'Status updated to '.$request->status.'.');
    }
}
