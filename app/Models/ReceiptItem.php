<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptItem extends Model
{
    protected $fillable = [
        'scanned_receipt_id',
        'item_fiscal_id',
        'name',
        'code',
        'unit',
        'quantity',
        'unit_price_before_vat',
        'unit_price_after_vat',
        'price_before_vat',
        'price_after_vat',
        'vat_rate',
        'vat_amount',
        'exempt_from_vat',
        'rebate',
        'rebate_reducing',
        'investment',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price_before_vat' => 'decimal:2',
        'unit_price_after_vat' => 'decimal:2',
        'price_before_vat' => 'decimal:2',
        'price_after_vat' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'rebate' => 'decimal:2',
        'exempt_from_vat' => 'boolean',
        'rebate_reducing' => 'boolean',
        'investment' => 'boolean',
    ];

    public function scannedReceipt(): BelongsTo
    {
        return $this->belongsTo(ScannedReceipt::class);
    }
}
