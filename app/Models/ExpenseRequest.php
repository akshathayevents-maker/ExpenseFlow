<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ExpenseRequest extends Model
{
    protected $fillable = [
        'title',
        'expense_category_id',
        'vendor_id',
        'requested_by',
        'approved_by',
        'amount',
        'notes',
        'qr_file_path',
        'priority',
        'status',
        'rejection_reason',
        'settlement_type',
        'approved_at',
        'whatsapp_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'approved_at'      => 'datetime',
            'whatsapp_sent_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(ExpenseBill::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(ExpensePayment::class);
    }

    public function walletTransaction(): HasOne
    {
        return $this->hasOne(WalletTransaction::class);
    }

    // Status helpers
    public function isPending(): bool              { return $this->status === 'pending'; }
    public function isPendingPayment(): bool        { return $this->status === 'pending_payment'; }
    public function isApproved(): bool             { return $this->status === 'approved'; }
    public function isRejected(): bool             { return $this->status === 'rejected'; }
    public function isPaid(): bool                 { return $this->status === 'paid'; }
    public function isReimbursementPending(): bool { return $this->status === 'reimbursement_pending'; }
    public function isReimbursed(): bool           { return $this->status === 'reimbursed'; }
    public function isCompleted(): bool            { return $this->status === 'completed'; }

    public function isSettled(): bool
    {
        return in_array($this->status, ['paid', 'reimbursement_pending', 'reimbursed', 'completed']);
    }

    /**
     * Signed URL to the QR-serving controller route.
     *
     * Used for <img src> in payment pages and authenticated employee views.
     * The controller streams the file directly — no nginx symlink, no
     * APP_URL dependency, no public/storage permissions needed.
     *
     * 30-day expiry matches paymentPageUrl() so both links expire together.
     */
    public function qrUrl(): ?string
    {
        if (! $this->qr_file_path) {
            return null;
        }

        return URL::temporarySignedRoute(
            'payment-request.serve-qr',
            now()->addDays(30),
            ['id' => $this->id],
        );
    }

    /**
     * Public (unsigned) storage URL — ONLY for og:image meta tags.
     *
     * WhatsApp/Facebook/Telegram link-preview crawlers (Facebot, WhatsAppBot)
     * cannot follow signed URLs because:
     *   1. They make an independent HTTP request to the og:image URL.
     *   2. The HMAC signature in qrUrl() is tied to server-side generation
     *      context — the crawler doesn't carry the payment page's signature.
     *
     * og:image is just a visual thumbnail — NOT a payment action. Exposing
     * the storage path here is acceptable because:
     *   • The URL reveals nothing about payment status or amounts.
     *   • File names are uniqid()-random, not guessable.
     *   • The QR itself is the UPI address, which is already public knowledge.
     *
     * Uses request()->getSchemeAndHttpHost() rather than APP_URL so the URL
     * is always correct regardless of .env configuration.
     */
    public function qrOgImageUrl(): ?string
    {
        if (! $this->qr_file_path) {
            return null;
        }

        try {
            $host = request()->getSchemeAndHttpHost();
        } catch (\Throwable) {
            $host = rtrim(config('app.url'), '/');
        }

        return $host . '/storage/' . ltrim($this->qr_file_path, '/');
    }

    public function paymentPageUrl(): string
    {
        return URL::temporarySignedRoute(
            'payment-request.show',
            now()->addDays(30),
            ['id' => $this->id],
        );
    }

    public function whatsAppUrl(): string
    {
        $name   = $this->requester?->name ?? 'Employee';
        $amount = number_format((float) $this->amount, 2);
        $link   = $this->paymentPageUrl();

        // PHP \u{XXXX} escapes guarantee correct UTF-8 bytes for supplementary emoji,
        // avoiding source-file encoding corruption. rawurlencode() is the PHP
        // equivalent of JS encodeURIComponent() — encodes the same character set.
        $party  = "\u{1F389}"; // 🎉
        $person = "\u{1F464}"; // 👤
        $bill   = "\u{1F9FE}"; // 🧾
        $money  = "\u{1F4B0}"; // 💰
        $mobile = "\u{1F4F2}"; // 📲
        $bolt   = "\u{26A1}";  // ⚡
        $rule   = "\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}\u{2501}";

        $lines = [
            "{$party} *Expense Payment Request*",
            "",
            "{$person} *Employee:* {$name}",
            "{$bill} *Title:* {$this->title}",
            "{$money} *Amount:* \u{20B9}{$amount}",
            "",
            $rule,
            "",
            "{$mobile} *Tap below to open payment page & scan QR:*",
            "",
            $link,
            "",
            $rule,
            "",
            "{$bolt} Shared via ExpenseFlow",
        ];

        return 'https://api.whatsapp.com/send?text=' . rawurlencode(implode("\n", $lines));
    }

    // Scopes
    public function scopePending($query)              { return $query->where('status', 'pending'); }
    public function scopePendingPayment($query)       { return $query->where('status', 'pending_payment'); }
    public function scopeApproved($query)             { return $query->where('status', 'approved'); }
    public function scopeRejected($query)             { return $query->where('status', 'rejected'); }
    public function scopeReimbursementPending($query) { return $query->where('status', 'reimbursement_pending'); }

    public static function statusColors(): array
    {
        return [
            'pending'               => 'warning',
            'pending_payment'       => 'info',
            'approved'              => 'success',
            'rejected'              => 'danger',
            'paid'                  => 'info',
            'reimbursement_pending' => 'primary',
            'reimbursed'            => 'teal',
            'completed'             => 'secondary',
        ];
    }

    public static function priorityColors(): array
    {
        return [
            'low'    => 'secondary',
            'medium' => 'info',
            'high'   => 'warning',
            'urgent' => 'danger',
        ];
    }
}
