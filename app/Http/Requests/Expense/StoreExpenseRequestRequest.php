<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreExpenseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'title'  => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'notes'  => ['nullable', 'string', 'max:2000'],
            'qr'     => ['required', 'file', 'max:20480'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $file = $this->file('qr');
            if (! $file || ! $file->isValid()) {
                return;
            }

            $mime = $file->getMimeType() ?? '';
            $ext  = strtolower($file->getClientOriginalExtension());

            $allowedMimes = [
                'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                'image/gif', 'image/bmp', 'image/heic', 'image/heif',
                'image/avif', 'image/tiff', 'application/pdf',
            ];
            $allowedExts = [
                'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp',
                'heic', 'heif', 'avif', 'tiff', 'tif', 'pdf',
            ];

            $mimeOk = in_array($mime, $allowedMimes, true) || str_starts_with($mime, 'image/');
            $extOk  = in_array($ext, $allowedExts, true);

            if (! $mimeOk && ! $extOk) {
                $v->errors()->add(
                    'qr',
                    "Unsupported file format ({$mime}). Please upload a JPG, PNG, WebP, HEIC, or PDF."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'qr.required' => 'Please upload a payment QR image.',
            'qr.file'     => 'The QR upload failed — please try again.',
            'qr.max'      => 'QR file must be under 20 MB.',
        ];
    }
}
