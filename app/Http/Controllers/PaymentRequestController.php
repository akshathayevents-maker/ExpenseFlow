<?php

namespace App\Http\Controllers;

use App\Models\ExpensePayment;
use App\Models\ExpenseRequest;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentRequestController extends Controller
{
    public function __construct(
        private AuditLogService     $auditLogService,
        private NotificationService $notificationService,
    ) {}

    /* ─────────────────────────────────────────────────────────────────
     | PUBLIC SHOW  (signed URL — no auth required)
     |
     | Security posture:
     |   • Route carries `middleware('signed')` — Laravel validates the
     |     HMAC signature + expiry before this method is called.
     |   • No auth is required to VIEW, so WhatsApp recipients (public)
     |     see the QR and payment status immediately.
     |   • Auth is checked HERE to decide which controls to render;
     |     the auth check never gates access to the page itself.
     ──────────────────────────────────────────────────────────────── */
    public function show(int $id): View
    {
        $expense = ExpenseRequest::with(['requester', 'approver', 'payment.payer'])
            ->findOrFail($id);

        $user    = auth()->user();
        $isStaff = $user && in_array($user->role, ['admin', 'manager'], true);

        // canAct: logged-in staff + expense is in an actionable (non-terminal) state
        $canAct  = $isStaff
            && ! $expense->isSettled()
            && ! $expense->isRejected();

        return view('payment-request.show', [
            'expense' => $expense,
            'isStaff' => $isStaff,
            'canAct'  => $canAct,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────
     | MARK AS PAID
     |
     | Auth is validated here (not at route level) so the route has no
     | `auth` middleware — this prevents session-loss failures when the
     | link is opened in a WhatsApp in-app browser that has no cookies.
     | CSRF is still enforced by the global `web` middleware group.
     |
     | Replay protection: isSettled() check + DB transaction.
     ──────────────────────────────────────────────────────────────── */
    public function markPaid(Request $request, int $id): RedirectResponse
    {
        $expense = ExpenseRequest::with(['requester', 'payment'])->findOrFail($id);

        // Auth gate (controller-level — intentionally not route-level)
        $user = auth()->user();
        if (! $user || ! Gate::forUser($user)->allows('markPaid', $expense)) {
            return $this->unauthorizedRedirect($request, $id);
        }

        // Idempotency — already settled
        if ($expense->isSettled()) {
            return $this->backToPayPage($id)->with('info', 'This request is already settled.');
        }

        $validated = $request->validate([
            'payment_mode'      => ['nullable', 'string', 'in:cash,upi,bank_transfer,wallet'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'payment_note'      => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($expense, $user, $validated) {
            $old = $expense->only('status');

            $expense->update([
                'status'          => 'paid',
                'settlement_type' => 'direct_payment',
                'approved_by'     => $user->id,
                'approved_at'     => now(),
            ]);

            // Create the ExpensePayment record (was missing before Phase 3)
            ExpensePayment::create([
                'expense_request_id'   => $expense->id,
                'payment_mode'         => $validated['payment_mode'] ?? 'upi',
                'amount'               => $expense->amount,
                'transaction_reference'=> $validated['payment_reference'] ?? null,
                'payment_notes'        => $validated['payment_note'] ?? null,
                'paid_by'              => $user->id,
                'paid_at'              => now(),
            ]);

            $this->auditLogService->log(
                'marked_paid',
                'expense_request',
                $expense->id,
                $expense->title,
                $old,
                array_filter([
                    'status'    => 'paid',
                    'by'        => $user->name,
                    'mode'      => $validated['payment_mode'] ?? 'upi',
                    'reference' => $validated['payment_reference'] ?? null,
                    'note'      => $validated['payment_note'] ?? null,
                ]),
            );

            $this->notificationService->send(
                $expense->requester,
                'expense_paid',
                'Payment Received',
                "Payment of \u{20B9}" . number_format($expense->amount, 2)
                    . " for \"{$expense->title}\" has been confirmed.",
                route('employee.expense-requests.show', $expense),
            );
        });

        return $this->backToPayPage($id)->with('paid_success', true);
    }

    /* ─────────────────────────────────────────────────────────────────
     | REJECT FROM PAYMENT PAGE
     ──────────────────────────────────────────────────────────────── */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $expense = ExpenseRequest::with('requester')->findOrFail($id);

        $user = auth()->user();
        if (! $user || ! Gate::forUser($user)->allows('rejectFromPayPage', $expense)) {
            return $this->unauthorizedRedirect($request, $id);
        }

        if ($expense->isSettled() || $expense->isRejected()) {
            return $this->backToPayPage($id)->with('info', 'Request cannot be rejected in its current state.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:300'],
        ]);

        $old = $expense->only('status');

        $expense->update([
            'status'           => 'rejected',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $this->auditLogService->log(
            'rejected_from_pay_page',
            'expense_request',
            $expense->id,
            $expense->title,
            $old,
            ['status' => 'rejected', 'reason' => $validated['rejection_reason'], 'by' => $user->name],
        );

        $this->notificationService->send(
            $expense->requester,
            'expense_rejected',
            'Payment Request Rejected',
            "Your payment request \"{$expense->title}\" was rejected: {$validated['rejection_reason']}",
            route('employee.expense-requests.show', $expense),
        );

        return $this->backToPayPage($id)->with('reject_success', true);
    }

    /* ─────────────────────────────────────────────────────────────────
     | UPLOAD PAYMENT PROOF
     |
     | Stored on the private disk (app/private or storage/app).
     | Proof is NOT served publicly — must be fetched via the
     | `proof` GET route which checks auth before streaming.
     ──────────────────────────────────────────────────────────────── */
    public function uploadProof(Request $request, int $id): RedirectResponse
    {
        $expense = ExpenseRequest::with('payment')->findOrFail($id);

        $user = auth()->user();
        if (! $user || ! in_array($user->role, ['admin', 'manager'], true)) {
            return $this->unauthorizedRedirect($request, $id);
        }

        $validated = $request->validate([
            'proof_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',   // 5 MB
            ],
        ]);

        $file = $validated['proof_file'];
        $ext  = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $path = "payment-proofs/{$id}/" . Str::random(24) . ".{$ext}";

        // Private disk — not publicly accessible
        $file->storeAs('', $path, 'local');

        // Upsert: one proof per expense (replace if re-uploaded)
        $payment = $expense->payment;

        if ($payment) {
            // Delete old proof file if one existed
            if ($payment->proof_file_path) {
                Storage::disk('local')->delete($payment->proof_file_path);
            }
            $payment->update(['proof_file_path' => $path]);
        } else {
            // Payment record may not exist yet (proof uploaded before mark-paid)
            ExpensePayment::create([
                'expense_request_id' => $expense->id,
                'payment_mode'       => 'upi',
                'amount'             => $expense->amount,
                'paid_by'            => $user->id,
                'paid_at'            => now(),
                'proof_file_path'    => $path,
            ]);
        }

        $this->auditLogService->log(
            'proof_uploaded',
            'expense_request',
            $expense->id,
            $expense->title,
            [],
            ['by' => $user->name, 'file' => basename($path)],
        );

        return $this->backToPayPage($id)->with('proof_success', true);
    }

    /* ─────────────────────────────────────────────────────────────────
     | SERVE PROOF FILE  (auth-gated, private storage)
     ──────────────────────────────────────────────────────────────── */
    public function serveProof(int $id): mixed
    {
        $user = auth()->user();
        if (! $user || ! in_array($user->role, ['admin', 'manager'], true)) {
            abort(403);
        }

        $expense = ExpenseRequest::with('payment')->findOrFail($id);
        $proof   = $expense->payment?->proof_file_path;

        if (! $proof || ! Storage::disk('local')->exists($proof)) {
            abort(404, 'Proof file not found.');
        }

        return response()->file(Storage::disk('local')->path($proof));
    }

    /* ─────────────────────────────────────────────────────────────────
     | HELPERS
     ──────────────────────────────────────────────────────────────── */

    /**
     * Redirect back to the public payment page.
     * We regenerate a fresh signed URL so the page loads correctly
     * even if the original link params are lost after POST redirect.
     */
    private function backToPayPage(int $id): RedirectResponse
    {
        $expense = ExpenseRequest::findOrFail($id);
        return redirect($expense->paymentPageUrl());
    }

    /**
     * Handle unauthorized access:
     *  - Not logged in  → redirect to login with intended URL stored in session.
     *  - Wrong role     → 403.
     */
    private function unauthorizedRedirect(Request $request, int $id): RedirectResponse
    {
        if (! auth()->check()) {
            // Store the current payment URL as intended so login returns here
            $expense = ExpenseRequest::findOrFail($id);
            session()->put('url.intended', $expense->paymentPageUrl());

            return redirect()->route('login')
                ->with('status', 'Please log in to access staff controls.');
        }

        abort(403, 'You do not have permission to perform this action.');
    }
}
