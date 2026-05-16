<?php

namespace App\Services;

use App\Models\ExpenseBill;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function storeBills(array $files, ExpenseRequest $request, User $uploader): void
    {
        foreach ($files as $file) {
            $path = $file->store("bills/{$request->id}", 'public');

            ExpenseBill::create([
                'expense_request_id' => $request->id,
                'file_path'          => $path,
                'original_name'      => $file->getClientOriginalName(),
                'mime_type'          => $file->getMimeType(),
                'file_size'          => $file->getSize(),
                'uploaded_by'        => $uploader->id,
            ]);
        }
    }

    public function deleteBill(ExpenseBill $bill): void
    {
        Storage::disk('public')->delete($bill->file_path);
        $bill->delete();
    }

    public function deleteAllBills(ExpenseRequest $request): void
    {
        foreach ($request->bills as $bill) {
            Storage::disk('public')->delete($bill->file_path);
        }

        Storage::disk('public')->deleteDirectory("bills/{$request->id}");
        $request->bills()->delete();
    }
}
