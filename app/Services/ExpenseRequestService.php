<?php

namespace App\Services;

use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseRequestService
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private NotificationService $notificationService,
        private AuditLogService $auditLogService,
    ) {}

    public function create(array $data, array $files, User $requester): ExpenseRequest
    {
        return DB::transaction(function () use ($data, $files, $requester) {
            $request = ExpenseRequest::create([
                'title'               => $data['title'],
                'expense_category_id' => $data['expense_category_id'],
                'vendor_id'           => $data['vendor_id'] ?? null,
                'requested_by'        => $requester->id,
                'amount'              => $data['amount'],
                'notes'               => $data['notes'] ?? null,
                'priority'            => $data['priority'],
                'status'              => 'pending',
            ]);

            if (! empty($files)) {
                $this->fileUploadService->storeBills($files, $request, $requester);
            }

            return $request;
        });
    }

    public function approve(ExpenseRequest $request, User $approver): void
    {
        $old = $request->only('status');

        $request->update([
            'status'           => 'approved',
            'approved_by'      => $approver->id,
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        $this->auditLogService->log('approved', 'expense_request', $request->id, $request->title, $old, ['status' => 'approved']);

        $this->notificationService->send(
            $request->requester,
            'expense_approved',
            'Expense Approved',
            "Your expense \"{$request->title}\" has been approved.",
            route('employee.expense-requests.show', $request),
        );
    }

    public function reject(ExpenseRequest $request, User $approver, string $reason): void
    {
        $old = $request->only('status');

        $request->update([
            'status'           => 'rejected',
            'approved_by'      => $approver->id,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditLogService->log('rejected', 'expense_request', $request->id, $request->title, $old, ['status' => 'rejected', 'reason' => $reason]);

        $this->notificationService->send(
            $request->requester,
            'expense_rejected',
            'Expense Rejected',
            "Your expense \"{$request->title}\" was rejected: {$reason}",
            route('employee.expense-requests.show', $request),
        );
    }

    public function markPaid(ExpenseRequest $request): void
    {
        $request->update(['status' => 'paid']);
    }

    public function markCompleted(ExpenseRequest $request): void
    {
        $request->update(['status' => 'completed']);
    }
}
