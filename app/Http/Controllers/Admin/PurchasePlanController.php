<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchasePlanRequest;
use App\Models\PurchasePlan;
use App\Services\AuditLogService;
use App\Services\PurchasePlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchasePlanController extends Controller
{
    public function __construct(
        private PurchasePlanningService $planningService,
        private AuditLogService         $audit
    ) {}

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['draft', 'approved', 'ordered', 'completed', 'cancelled'], true)
            ? $request->query('status') : null;
        $plans = PurchasePlan::with(['creator', 'items'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'draft'     => PurchasePlan::where('status', 'draft')->count(),
            'approved'  => PurchasePlan::where('status', 'approved')->count(),
            'ordered'   => PurchasePlan::where('status', 'ordered')->count(),
            'completed' => PurchasePlan::where('status', 'completed')->count(),
            'cancelled' => PurchasePlan::where('status', 'cancelled')->count(),
            'total'     => PurchasePlan::count(),
        ];

        return view('admin.purchase-plans.index', compact('plans', 'stats', 'status'));
    }

    public function suggestions(): View
    {
        $suggestions = $this->planningService->getSuggestions();
        return view('admin.purchase-plans.suggestions', compact('suggestions'));
    }

    public function create(): View
    {
        $suggestions = $this->planningService->getSuggestions();
        return view('admin.purchase-plans.create', compact('suggestions'));
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
            'ordered'   => ['approved'],
            'completed' => ['ordered'],
            'cancelled' => ['draft', 'approved', 'ordered'],
        ][$request->status];
        if (! in_array($purchasePlan->status, $allowed, true)) {
            return back()->with('error', 'A '.$purchasePlan->status.' plan cannot be marked '.$request->status.'.');
        }
        $purchasePlan->update(['status' => $request->status]);
        $this->audit->log($request->status, 'purchase_plan', $purchasePlan->id, $purchasePlan->title);
        return back()->with('success', 'Status updated to ' . $request->status . '.');
    }
}
