<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\HallBooking;
use App\Models\InventoryItem;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\MealEntry;
use App\Models\MealEntryItem;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Inventory\MaterialRequestService;
use App\Services\Inventory\RecipeStandardService;
use App\Services\Inventory\UnitConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class MaterialRequestController extends Controller
{
    public function __construct(
        private MaterialRequestService $service,
        private UnitConversionService $units,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MaterialRequest::class);
        $user = $request->user();
        $tabs = ['mine' => 'My requests'];
        if ($user->isAdmin()) {
            $tabs['approve'] = 'To approve';
            $tabs['review'] = 'Usage review';
        }
        if ($user->isStorekeeper()) {
            $tabs['handover'] = 'Ready for handover';
            $tabs['out'] = 'Out / returns';
        }
        if ($user->isAdmin() || $user->isManager() || $user->isStorekeeper()) {
            $tabs['all'] = 'All';
        }
        $tab = array_key_exists($request->input('tab'), $tabs) ? $request->input('tab') : array_key_first($tabs);

        $q = MaterialRequest::with(['requester', 'mealEntry.client', 'hallBooking'])->withCount('items');
        match ($tab) {
            'approve' => $q->where('status', 'submitted')->orderBy('required_date'),
            'review' => $q->where('reconciliation_status', 'pending_review')->latest('reconciled_at'),
            'handover' => $q->where('status', 'approved')->orderBy('required_date')->orderBy('required_time'),
            'out' => $q->where('status', 'issued')->orderBy('required_date'),
            'all' => $q->latest('required_date')->latest('id'),
            default => $q->where('requested_by', $user->id)->latest('id'),
        };
        $requests = $q->paginate(25)->withQueryString();

        $counts = [
            'approve' => $user->isAdmin() ? MaterialRequest::where('status', 'submitted')->count() : 0,
            'review' => $user->isAdmin() ? MaterialRequest::where('reconciliation_status', 'pending_review')->count() : 0,
            'handover' => $user->isStorekeeper() ? MaterialRequest::where('status', 'approved')->count() : 0,
            'out' => $user->isStorekeeper() ? MaterialRequest::where('status', 'issued')->count() : 0,
        ];

        return view('inventory.material-requests.index', compact('requests', 'tabs', 'tab', 'counts'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', MaterialRequest::class);
        $prefill = ['source' => 'other', 'required_date' => now('Asia/Kolkata')->addDay()->toDateString(), 'department' => 'kitchen'];

        if ($request->filled('meal_entry_id')) {
            $entry = MealEntry::with('items', 'client')->findOrFail($request->integer('meal_entry_id'));
            $type = $request->input('meal_type');
            $line = $entry->items->firstWhere('meal_type', $type);
            $prefill = array_merge($prefill, [
                'source' => 'meal', 'meal_entry_id' => $entry->id, 'meal_type' => $type,
                'required_date' => $entry->entry_date->toDateString(),
                'guest_count' => $line?->planned_count ?? $line?->actual_count,
            ]);
        }
        if ($request->filled('hall_booking_id')) {
            $b = HallBooking::findOrFail($request->integer('hall_booking_id'));
            $prefill = array_merge($prefill, [
                'source' => 'hall', 'hall_booking_id' => $b->id, 'department' => 'hall',
                'required_date' => $b->booking_date->toDateString(), 'guest_count' => $b->number_of_people,
            ]);
        }

        return view('inventory.material-requests.form', $this->formData(null, $prefill, $request->user()));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MaterialRequest::class);
        try {
            $mr = $this->service->save($this->validated($request), $request->user(), null, $request->input('action') === 'submit');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.material-requests.show', $mr)
            ->with('success', $mr->status === 'submitted' ? 'Request sent for approval.' : 'Draft saved.');
    }

    public function show(MaterialRequest $materialRequest): View
    {
        $this->authorize('view', $materialRequest);
        $mr = $materialRequest->load([
            'items.item', 'requester', 'approver', 'handedOverBy', 'collector', 'canceller', 'reconciler', 'reviewer',
            'mealEntry.client', 'hallBooking', 'revisionOf', 'revisions',
        ]);

        // what was already issued for the same meal / hall order (other requests)
        $previous = collect();
        if ($mr->meal_entry_id || $mr->hall_booking_id) {
            $previous = MaterialRequestItem::query()
                ->whereHas('request', fn ($q) => $q->where('id', '!=', $mr->id)
                    ->whereIn('status', ['approved', 'issued', 'reconciled'])
                    ->when($mr->meal_entry_id, fn ($w) => $w->where('meal_entry_id', $mr->meal_entry_id)->where('meal_type', $mr->meal_type))
                    ->when($mr->hall_booking_id, fn ($w) => $w->where('hall_booking_id', $mr->hall_booking_id)))
                ->selectRaw('inventory_item_id, SUM(COALESCE(issued_base_quantity, approved_base_quantity)) as qty')
                ->groupBy('inventory_item_id')->pluck('qty', 'inventory_item_id');
        }
        $staff = $mr->status === 'approved'
            ? User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('inventory.material-requests.show', compact('mr', 'previous', 'staff'));
    }

    public function edit(MaterialRequest $materialRequest): View
    {
        $this->authorize('update', $materialRequest);
        $mr = $materialRequest->load('items');

        return view('inventory.material-requests.form', $this->formData($mr, $mr->only([
            'source', 'meal_entry_id', 'meal_type', 'hall_booking_id', 'department', 'required_time',
            'preparation_name', 'guest_count', 'purpose', 'notes',
        ]) + ['required_date' => $mr->required_date->toDateString()], request()->user()));
    }

    public function update(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('update', $materialRequest);
        try {
            $mr = $this->service->save($this->validated($request), $request->user(), $materialRequest, $request->input('action') === 'submit');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.material-requests.show', $mr)->with('success', 'Request updated.');
    }

    public function submit(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('submit', $materialRequest);

        return $this->run(fn () => $this->service->submit($materialRequest, $request->user()), $materialRequest, 'Sent for approval.');
    }

    public function approve(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('approve', $materialRequest);
        $data = $request->validate([
            'lines' => ['nullable', 'array'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.remark' => ['nullable', 'string', 'max:300'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->run(fn () => $this->service->approve($materialRequest, $request->user(), $data['lines'] ?? [], $data['remarks'] ?? null),
            $materialRequest, 'Approved. Stock has been deducted and the store has been told.');
    }

    public function reject(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('reject', $materialRequest);
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        return $this->run(fn () => $this->service->reject($materialRequest, $request->user(), $data['reason']), $materialRequest, 'Request rejected.');
    }

    public function handover(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('handover', $materialRequest);
        $data = $request->validate([
            'collected_by' => ['nullable', 'integer', 'exists:users,id'],
            'collected_by_name' => ['nullable', 'string', 'max:120'],
            'issued' => ['nullable', 'array'],
            'issued.*' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->run(fn () => $this->service->handover($materialRequest, $request->user(), $data['collected_by'] ?? null,
            $data['collected_by_name'] ?? null, $data['issued'] ?? [], $data['notes'] ?? null), $materialRequest, 'Handover recorded.');
    }

    public function cancel(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('cancel', $materialRequest);
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        return $this->run(fn () => $this->service->cancel($materialRequest, $request->user(), $data['reason']), $materialRequest, 'Request cancelled.');
    }

    public function returns(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('recordReturn', $materialRequest);
        $data = $request->validate([
            'returns' => ['required', 'array'],
            'returns.*' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        return $this->run(fn () => $this->service->recordReturn($materialRequest, $request->user(), $data['returns'], $data['note'] ?? null),
            $materialRequest, 'Return recorded and stock updated.');
    }

    public function reconcile(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('reconcile', $materialRequest);
        $data = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.consumed' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.wasted' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.wastage_reason' => ['nullable', 'string', 'max:300'],
            'lines.*.variance_reason' => ['nullable', 'string', 'max:300'],
        ]);

        return $this->run(fn () => $this->service->reconcile($materialRequest, $request->user(), $data['lines']), $materialRequest, 'Usage recorded.');
    }

    public function review(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('review', $materialRequest);
        $data = $request->validate(['remarks' => ['required', 'string', 'min:3', 'max:500']]);

        return $this->run(fn () => $this->service->reviewReconciliation($materialRequest, $request->user(), $data['remarks']), $materialRequest, 'Review recorded.');
    }

    public function revise(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorize('revise', $materialRequest);
        try {
            $new = $this->service->revise($materialRequest->load('items'), $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.material-requests.edit', $new)
            ->with('success', "Revision {$new->reference()} started. The original stays unchanged.");
    }

    // ── helpers ────────────────────────────────────────────────────────────

    private function run(callable $fn, MaterialRequest $mr, string $ok): RedirectResponse
    {
        try {
            $fn();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.material-requests.show', $mr)->with('success', $ok);
    }

    private function validated(Request $request): array
    {
        $max = (int) config('inventory.max_lines', 100);

        return $request->validate([
            'source' => ['required', 'in:meal,hall,other'],
            'meal_entry_id' => ['nullable', 'required_if:source,meal', 'integer'],
            'meal_type' => ['nullable', 'required_if:source,meal', Rule::in(array_keys(MealEntryItem::mealTypes() + ['reception' => 1, 'high_tea' => 1, 'snacks' => 1]))],
            'hall_booking_id' => ['nullable', 'required_if:source,hall', 'integer'],
            'department' => ['required', Rule::in(array_keys(MaterialRequest::DEPARTMENTS))],
            'required_date' => ['required', 'date', 'after_or_equal:'.now('Asia/Kolkata')->subDays(7)->toDateString()],
            'required_time' => ['nullable', 'date_format:H:i'],
            'preparation_name' => ['nullable', 'string', 'max:150'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'purpose' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'client_token' => ['nullable', 'uuid'],
            'recipe_id' => ['nullable', 'integer', 'exists:recipes,id'],
            'action' => ['nullable', 'in:draft,submit'],
            'lines' => ['required', 'array', 'min:1', "max:{$max}"],
            'lines.*.inventory_item_id' => ['nullable', 'integer'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.unit' => ['nullable', 'string', 'max:20'],
        ], ['meal_entry_id.required_if' => 'Choose the meal.', 'hall_booking_id.required_if' => 'Choose the hall order.']);
    }

    private function formData(?MaterialRequest $mr, array $prefill, User $user): array
    {
        $lines = old('lines') ?: ($mr ? $mr->items->map(fn ($i) => [
            'inventory_item_id' => $i->inventory_item_id, 'quantity' => $i->requested_quantity, 'unit' => $i->requested_unit,
        ])->all() : []);

        // Suggested quantities from a standard recipe version (never applied silently: shown, editable).
        $suggestion = null;
        $guests = (int) (request('guest_count') ?: ($prefill['guest_count'] ?? 0));
        if (! $mr && $guests > 0 && request()->filled('recipe_id')) {
            $suggestion = app(RecipeStandardService::class)->suggest((int) request('recipe_id'), $guests);
            if ($suggestion) {
                $prefill['guest_count'] = $guests;
                $prefill['recipe_id'] = $suggestion['recipe_id'];
                $prefill['preparation_name'] = $prefill['preparation_name'] ?? $suggestion['recipe'];
                if (! old('lines')) {
                    $lines = $suggestion['lines'];
                }
            }
        }

        $today = now('Asia/Kolkata')->toDateString();

        return [
            'mr' => $mr,
            'prefill' => $prefill,
            'lines' => array_values($lines),
            'catalog' => $this->catalog(),
            'quickItems' => $this->quickItems($user),
            'mealEntries' => MealEntry::with('client')->whereBetween('entry_date', [now('Asia/Kolkata')->subDays(2)->toDateString(), now('Asia/Kolkata')->addDays(7)->toDateString()])
                ->orderBy('entry_date')->get(),
            'bookings' => HallBooking::where('status', '!=', 'cancelled')->whereIn('booking_type', ['hall_food', 'food_only'])
                ->whereBetween('booking_date', [now('Asia/Kolkata')->subDays(2)->toDateString(), now('Asia/Kolkata')->addDays(30)->toDateString()])
                ->orderBy('booking_date')->get(['id', 'customer_name', 'event_type', 'booking_date', 'number_of_people']),
            'mealTypes' => MealEntryItem::mealTypes(),
            'recipes' => Recipe::whereNull('superseded_at')->whereHas('ingredients', fn ($q) => $q->whereNotNull('quantity_per_guest'))->orderBy('name')->get(['id', 'name', 'version']),
            'suggestion' => $suggestion,
            'token' => (string) Str::uuid(),
            'today' => $today,
        ];
    }

    private function catalog(): array
    {
        return InventoryItem::with(['unitConversions', 'aliases:id,inventory_item_id,alias'])->where('status', 'active')->orderBy('name')->get()
            ->map(fn ($i) => [
                'id' => $i->id, 'name' => $i->name, 'unit' => $i->unit, 'units' => $this->units->unitsFor($i),
                'stock' => round((float) $i->current_stock, 3), 'aliases' => $i->aliases->pluck('alias')->take(8)->all(),
            ])->all();
    }

    /** Recently and frequently requested items for this user (last 60 days). */
    private function quickItems(User $user): array
    {
        $rows = MaterialRequestItem::query()
            ->whereHas('request', fn ($q) => $q->where('requested_by', $user->id)->where('created_at', '>=', now()->subDays(60)))
            ->selectRaw('inventory_item_id, COUNT(*) as uses, MAX(id) as last_id')
            ->groupBy('inventory_item_id')->get();
        $names = InventoryItem::whereIn('id', $rows->pluck('inventory_item_id'))->pluck('name', 'id');

        return [
            'recent' => $rows->sortByDesc('last_id')->take(8)->map(fn ($r) => ['id' => $r->inventory_item_id, 'name' => $names[$r->inventory_item_id] ?? '?'])->values()->all(),
            'frequent' => $rows->sortByDesc('uses')->take(8)->map(fn ($r) => ['id' => $r->inventory_item_id, 'name' => $names[$r->inventory_item_id] ?? '?'])->values()->all(),
        ];
    }
}
