<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBillUpload extends Model
{
    protected $fillable = [
        'vendor_name', 'invoice_number', 'invoice_date', 'gst_number',
        'subtotal', 'tax_amount', 'total_amount',
        'original_filename', 'stored_path', 'file_type', 'file_hash',
        'extracted_json', 'ocr_provider', 'status', 'notes',
        'uploaded_by', 'reviewed_by',
    ];

    protected $casts = [
        'invoice_date'   => 'date',
        'subtotal'       => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'total_amount'   => 'decimal:2',
        'extracted_json' => 'array',
    ];

    public function uploader(): BelongsTo  { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function reviewer(): BelongsTo  { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function items(): HasMany       { return $this->hasMany(InventoryBillItem::class, 'bill_upload_id'); }

    public function isImage(): bool  { return $this->file_type === 'image'; }
    public function isPdf(): bool    { return $this->file_type === 'pdf'; }
    public function isImported(): bool { return $this->status === 'imported'; }
    public function canImport(): bool  { return in_array($this->status, ['review_pending', 'extracted']); }
    public function canDelete(): bool  { return $this->status !== 'imported'; }

    public static function statuses(): array
    {
        return [
            'uploaded'       => 'secondary',
            'processing'     => 'info',
            'extracted'      => 'primary',
            'review_pending' => 'warning',
            'imported'       => 'success',
            'failed'         => 'danger',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'uploaded'       => 'Uploaded',
            'processing'     => 'Processing',
            'extracted'      => 'Extracted',
            'review_pending' => 'Pending Review',
            'imported'       => 'Imported',
            'failed'         => 'Failed',
        ];
    }

    public function duplicateCheck(): ?self
    {
        if (! $this->invoice_number) return null;

        return static::where('id', '!=', $this->id)
            ->where('invoice_number', $this->invoice_number)
            ->where('vendor_name', $this->vendor_name)
            ->first();
    }
}
