<?php

namespace App\Http\Controllers;

use App\Models\ExpenseRequest;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentRequestController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
        private NotificationService $notificationService,
    ) {}

    public function show(int $id): View
    {
        $expense = ExpenseRequest::with('requester')->findOrFail($id);

        return view('payment-request.show', compact('expense'));
    }

    public function markPaid(Request $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $user = auth()->user();

        if (! $user || ! in_array($user->role, ['admin', 'manager'], true)) {
            abort(403);
        }

        if ($expenseRequest->isSettled()) {
            return back()->with('info', 'This request is already settled.');
        }

        $validated = $request->validate([
            'payment_note'      => ['nullable', 'string', 'max:500'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $old = $expenseRequest->only('status');

        $expenseRequest->update([
            'status'          => 'paid',
            'settlement_type' => 'direct_payment',
            'approved_by'     => $user->id,
            'approved_at'     => now(),
        ]);

        $this->auditLogService->log(
            'marked_paid',
            'expense_request',
            $expenseRequest->id,
            $expenseRequest->title,
            $old,
            array_filter([
                'status'    => 'paid',
                'by'        => $user->name,
                'note'      => $validated['payment_note'] ?? null,
                'reference' => $validated['payment_reference'] ?? null,
            ]),
        );

        $this->notificationService->send(
            $expenseRequest->requester,
            'expense_paid',
            'Payment Received',
            "Payment of ₹" . number_format($expenseRequest->amount, 2) . " for \"{$expenseRequest->title}\" has been confirmed.",
            route('employee.expense-requests.show', $expenseRequest),
        );

        return back()->with('paid_success', true);
    }
}
