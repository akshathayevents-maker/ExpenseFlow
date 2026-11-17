<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestItem extends Model
{
    protected $fillable = [
        'material_request_id', 'inventory_item_id', 'requested_quantity', 'requested_unit',
        'requested_base_quantity', 'approved_base_quantity', 'line_status', 'admin_remark',
        'issue_transaction_id', 'issued_base_quantity', 'returned_base_quantity',
        'consumed_base_quantity', 'wasted_base_quantity', 'unexplained_base_quantity',
        'wastage_reason', 'variance_reason', 'unit_cost', 'sort_order', 'suggested_base_quantity',
    ];

    protected $casts = [
        'requested_quantity' => 'float',
        'requested_base_quantity' => 'float',
        'approved_base_quantity' => 'float',
        'issued_base_quantity' => 'float',
        'returned_base_quantity' => 'float',
        'consumed_base_quantity' => 'float',
        'wasted_base_quantity' => 'float',
        'unexplained_base_quantity' => 'float',
        'unit_cost' => 'float',
        'suggested_base_quantity' => 'float',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class, 'material_request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function issueTransaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'issue_transaction_id');
    }

    /** Quantity that left the store and has not come back. */
    public function netIssued(): float
    {
        return max(0, round(($this->issued_base_quantity ?? 0) - $this->returned_base_quantity, 3));
    }
}
