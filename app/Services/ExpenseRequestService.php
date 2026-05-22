<?php

namespace App\Services;

use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExpenseRequestService
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private NotificationService $notificationService,
        private AuditLogService $auditLogService,
    ) {}

    public function create(array $data, User $requester, ?\Illuminate\Http\UploadedFile $qrFile = null): ExpenseRequest
    {
        return DB::transaction(function () use ($data, $requester, $qrFile) {
            // Create the record first so we have an ID for the permanent path
            $expense = ExpenseRequest::create([
                'title'        => $data['title'],
                'requested_by' => $requester->id,
                'amount'       => $data['amount'],
                'notes'        => $data['notes'] ?? null,
                'qr_file_path' => null,
                'status'       => 'pending_payment',
            ]);

            if ($qrFile) {
                try {
                    $ext      = strtolower($qrFile->getClientOriginalExtension()) ?: 'jpg';
                    $safeName = Str::slug(pathinfo($qrFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $safeName = substr($safeName ?: 'qr', 0, 40);
                    // Permanent path keyed by expense ID — never auto-deleted
                    $filename = "qr-codes/{$expense->id}/{$safeName}_" . uniqid() . ".{$ext}";

                    $stored = $qrFile->storeAs('', $filename, 'public');

                    if ($stored === false) {
                        throw new \RuntimeException('Storage::storeAs returned false.');
                    }

                    $expense->update(['qr_file_path' => $filename]);
                } catch (\Throwable $e) {
                    Log::error('QR file storage failed', [
                        'expense_id' => $expense->id,
                        'user'       => $requester->id,
                        'file'       => $qrFile->getClientOriginalName(),
                        'mime'       => $qrFile->getMimeType(),
                        'size'       => $qrFile->getSize(),
                        'exception'  => $e->getMessage(),
                    ]);
                    throw new \RuntimeException('The QR image could not be saved. Please try again.', 0, $e);
                }
            }

            return $expense->fresh();
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
