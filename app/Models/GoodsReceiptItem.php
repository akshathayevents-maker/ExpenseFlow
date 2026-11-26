<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id', 'purchase_item_id', 'received_base_quantity',
        'rejected_base_quantity', 'actual_rate', 'closed_base_quantity', 'remarks', 'inventory_transaction_id',
    ];

    protected $casts = ['received_base_quantity' => 'float', 'rejected_base_quantity' => 'float',
        'actual_rate' => 'float', 'closed_base_quantity' => 'float'];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'inventory_transaction_id');
    }
}
