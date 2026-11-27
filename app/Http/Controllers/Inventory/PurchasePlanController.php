<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\PurchasePlan;
use App\Models\Vendor;
use App\Services\Inventory\PurchasePlanWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/** Staff-facing purchase plans: prepare → send for approval → buy (purchases) → receive (existing flow) → close. */
class PurchasePlanController extends Controller
{
    public function __construct(private PurchasePlanWorkflowService $flow) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchasePlan::class);
        $u = $request->user();
        $all = $u->isAdmin() || $u->isManager() || $u->isStorekeeper();
        $tab = in_array($request->query('tab'), ['mine', 'approve', 'buy', 'done', 'all'], true) ? $request->query('tab') : ($u->isAdmin() ? 'approve' : 'mine');
        $base = PurchasePlan::query()->when(! $all, fn ($q) => $q->where('created_by', $u->id));
        $counts = [
            'mine' => (clone $base)->where('created_by', $u->id)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'approve' => (clone $base)->where('status', 'submitted')->count(),
            'buy' => (clone $base)->whereIn('status', ['approved', 'ordered'])->count(),
            'done' => (clone $base)->whereIn('status', ['completed', 'cancelled'])->count(),
            'all' => (clone $base)->count(),
        ];
        $plans = (clone $base)->with('creator:id,name')->withCount('items')->withCount('purchases')
            ->when($tab === 'mine', fn ($q) => $q->where('created_by', $u->id)->whereNotIn('status', ['completed', 'cancelled']))
            ->when($tab === 'approve', fn ($q) => $q->where('status', 'submitted'))
            ->when($tab === 'buy', fn ($q) => $q->whereIn('status', ['approved', 'ordered']))
            ->when($tab === 'done', fn ($q) => $q->whereIn('status', ['completed', 'cancelled']))
            ->latest('id')->paginate(20)->withQueryString();

        return view('inventory.plans.index', compact('plans', 'counts', 'tab', 'all'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PurchasePlan::class);

        return view('inventory.plans.form', ['plan' => null, 'rows' => self::formRows(null)]);
    }

    /**
     * Rows for the plan form (the original "tick the suggested items" page): low-stock suggestions, plus — when
     * editing — the plan's own lines (ticked). Old input after a failed save wins.
     */
    public static function formRows(?PurchasePlan $plan): array
    {
        $sug = app(\App\Services\PurchasePlanningService::class)->getSuggestions();
        $rows = [];
        if ($plan) {
            $plan->loadMissing('items.inventoryItem');
            foreach ($plan->items as $it) {
                $i = $it->inventoryItem;
                $rows[$it->inventory_item_id] = ['inventory_item_id' => $it->inventory_item_id, 'selected' => true, 'name' => $i?->name, 'unit' => $i?->unit,
                    'stock' => (float) ($i?->current_stock ?? 0), 'min' => (float) ($i?->minimum_stock ?? 0),
                    'quantity' => $it->suggested_quantity, 'unit_cost' => $it->estimated_unit_cost, 'priority' => $it->priority, 'notes' => $it->notes];
            }
        }
        foreach ($sug as $i) {
            // suggestions start unticked: the user ticks just what they want to buy
            $rows[$i->id] ??= ['inventory_item_id' => $i->id, 'selected' => false, 'name' => $i->name, 'unit' => $i->unit,
                'stock' => (float) $i->current_stock, 'min' => (float) $i->minimum_stock, 'quantity' => $i->suggested_quantity,
                'unit_cost' => $i->average_cost, 'priority' => $i->priority, 'notes' => null, 'suggested' => true];
        }
        if ($old = old('items')) {
            $items = \App\Models\InventoryItem::whereIn('id', array_filter(array_column($old, 'inventory_item_id')))->get()->keyBy('id');
            $rows = [];
            foreach ($old as $o) {
                $i = $items[$o['inventory_item_id'] ?? 0] ?? null;
                if ($i) {
                    $rows[$i->id] = ['inventory_item_id' => $i->id, 'selected' => ! empty($o['selected']), 'name' => $i->name, 'unit' => $i->unit,
                        'stock' => (float) $i->current_stock, 'min' => (float) $i->minimum_stock, 'quantity' => $o['quantity'] ?? null,
                        'unit_cost' => $o['unit_cost'] ?? null, 'priority' => $o['priority'] ?? 'normal', 'notes' => $o['notes'] ?? null];
                }
            }
        }

        return array_values($rows);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchasePlan::class);
        $plan = $this->flow->save($this->validated($request), $request->user(), null, $request->boolean('submit'));

        return redirect()->route('inventory.plans.show', $plan)
            ->with('success', $plan->status === 'submitted' ? 'Plan sent for approval.' : 'Plan saved as draft.');
    }

    public function show(PurchasePlan $plan): View
    {
        $this->authorize('view', $plan);
        $plan->load(['items.inventoryItem', 'creator:id,name', 'approver:id,name', 'rejecter:id,name', 'closer:id,name', 'shortfallPlan:id,title',
            'purchases' => fn ($q) => $q->with('vendor:id,name')->withCount('items')->latest('id')]);

        return view('inventory.plans.show', [
            'plan' => $plan,
            'progress' => $this->flow->progress($plan),
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(PurchasePlan $plan): View
    {
        $this->authorize('update', $plan);

        return view('inventory.plans.form', ['plan' => $plan, 'rows' => self::formRows($plan)]);
    }

    public function update(Request $request, PurchasePlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        return $this->attempt(fn () => $this->flow->save($this->validated($request), $request->user(), $plan, $request->boolean('submit')),
            fn ($p) => redirect()->route('inventory.plans.show', $p)->with('success', $p->status === 'submitted' ? 'Plan sent for approval.' : 'Plan saved.'));
    }

    public function submit(Request $request, PurchasePlan $plan): RedirectResponse
    {
        $this->authorize('submit', $plan);

        return $this->attempt(fn () => $this->flow->submit($plan, $request->user()), fn () => back()->with('success', 'Plan sent for approval.'));
    }

    public function approve(Request $request, PurchasePlan $plan): RedirectResponse
    {
        $this->authorize('approve', $plan);
        $data = $request->validate([
            'approved' => ['nullable', 'array'], 'approved.*' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'approval_note' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->attempt(fn () => $this->flow->approve($plan, $request->user(), $data['approved'] ?? [], $data['approval_note'] ?? null),
            fn () => back()->with('success', 'Plan approved — it can now be bought.'));
    }

    public function reject(Request $request, PurchasePlan $plan): RedirectResponse
    {
        $this->authorize('reject', $plan);
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        return $this->attempt(fn () => $this->flow->reject($plan, $request->user(), $data['reason']), fn () => back()->with('success', 'Plan sent back to the creator.'));
    }

    public function cancel(Request $request, PurchasePlan $plan): RedirectResponse
    {
        $this->authorize('cancel', $plan);

        return $this->attempt(fn () => $this->flow->cancel($plan, $request->user()), fn () => back()->with('success', 'Plan cancelled.'));
    }

    public function buy(Request $request, PurchasePlan $plan): RedirectResponse
    {
        $this->authorize('buy', $plan);
        $data = $request->validate([
            'purchase_date' => ['required', 'date', 'before_or_equal:'.now()->addDays(30)->toDateString()],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'supplier_name' => ['nullable', 'string', 'max:150', 'required_without:vendor_id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'client_token' => ['nullable', 'uuid'],
            'lines' => ['required', 'array', 'max:200'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
        ], ['supplier_name.required_without' => 'Choose a supplier or type the shop name.']);

        return $this->attempt(fn () => $this->flow->createPurchase($plan, $request->user(), $data),
            fn ($purchase) => redirect()->route('inventory.purchases.show', $purchase)
                ->with('success', "Purchase {$purchase->reference()} created from {$plan->reference()} — receive the goods here when they arrive."));
    }

    public function close(Request $request, PurchasePlan $plan): RedirectResponse
    {
        $this->authorize('close', $plan);
        $data = $request->validate(['closing_note' => ['nullable', 'string', 'max:1000'], 'replan' => ['nullable', 'boolean']]);

        return $this->attempt(fn () => $this->flow->close($plan, $request->user(), $data['closing_note'] ?? null, $request->boolean('replan')),
            fn ($p) => $p->shortfall_plan_id
                ? redirect()->route('inventory.plans.show', $p->shortfall_plan_id)->with('success', 'Plan closed. The shortfall is in this new draft plan — check it and send for approval.')
                : back()->with('success', 'Plan closed.'));
    }

    /** The form posts items[i][selected|inventory_item_id|quantity|unit_cost|priority|notes]; only ticked rows count. */
    private function validated(Request $request): array
    {
        $d = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'planned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'max:300'],
            'items.*.selected' => ['nullable', 'boolean'],
            'items.*.inventory_item_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'items.*.priority' => ['nullable', 'in:urgent,high,normal,low'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);
        $d['lines'] = array_values(array_map(fn ($r) => ['inventory_item_id' => $r['inventory_item_id'] ?? null, 'quantity' => $r['quantity'] ?? null,
            'estimated_unit_cost' => $r['unit_cost'] ?? null, 'priority' => $r['priority'] ?? 'normal', 'notes' => $r['notes'] ?? null],
            array_filter($d['items'], fn ($r) => ! empty($r['selected']))));
        if (! $d['lines']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['items' => 'Tick at least one item.']);
        }

        return $d;
    }

    private function attempt(callable $do, callable $then): RedirectResponse
    {
        try {
            return $then($do());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
