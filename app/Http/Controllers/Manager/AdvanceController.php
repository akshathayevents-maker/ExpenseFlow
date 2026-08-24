<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Advance\ApproveAdvanceRequest;
use App\Http\Requests\Advance\RecordRepaymentRequest;
use App\Models\EmployeeAdvance;
use App\Services\EmployeeAdvanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdvanceController extends Controller
{
    public function __construct(private EmployeeAdvanceService $service) {}

    public function index(): View
    {
        $advances = EmployeeAdvance::with('user')->latest()->get();

        return view('manager.advances.index', compact('advances'));
    }

    public function show(EmployeeAdvance $advance): View
    {
        $this->authorize('view', $advance);

        $advance->load(['transactions' => fn ($q) => $q->latest('transaction_date')->latest('id'), 'user', 'approver', 'payer']);

        return view('manager.advances.show', compact('advance'));
    }

    public function approve(ApproveAdvanceRequest $request, EmployeeAdvance $advance): RedirectResponse
    {
        $this->authorize('approve', $advance);

        $this->service->approve($advance, auth()->user(), (float) $request->validated('approved_amount'));

        return back()->with('success', 'Advance approved.');
    }

    public function reject(EmployeeAdvance $advance): RedirectResponse
    {
        $this->authorize('reject', $advance);

        $this->service->reject($advance, auth()->user());

        return back()->with('success', 'Advance rejected.');
    }

    public function disburse(EmployeeAdvance $advance): RedirectResponse
    {
        $this->authorize('disburse', $advance);

        $this->service->disburse($advance, auth()->user());

        return back()->with('success', 'Advance disbursed.');
    }

    public function recordRepayment(RecordRepaymentRequest $request, EmployeeAdvance $advance): RedirectResponse
    {
        $this->authorize('recordRepayment', $advance);

        $this->service->recordRepayment(
            $advance,
            auth()->user(),
            (float) $request->validated('amount'),
            $request->validated('reference'),
        );

        return back()->with('success', 'Repayment recorded.');
    }
}
