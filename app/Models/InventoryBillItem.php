<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBillItem extends Model
{
    protected $fillable = [
        'bill_upload_id', 'inventory_item_id', 'category_id',
        'item_name', 'sku', 'quantity', 'unit',
        'unit_price', 'tax_percent', 'total',
        'raw_extracted_text', 'imported',
    ];

    protected $casts = [
        'quantity'   => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_percent'=> 'decimal:2',
        'total'      => 'decimal:2',
        'imported'   => 'boolean',
    ];

    public function bill(): BelongsTo          { return $this->belongsTo(InventoryBillUpload::class, 'bill_upload_id'); }
    public function inventoryItem(): BelongsTo { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function category(): BelongsTo      { return $this->belongsTo(InventoryCategory::class, 'category_id'); }
}
