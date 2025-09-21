<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptTaxSummary extends Model
{
    protected $fillable = [
        'scanned_receipt_id',
        'tax_fiscal_id',
        'number_of_items',
        'price_before_vat',
        'vat_rate',
        'vat_amount',
        'exempt_from_vat',
    ];

    protected $casts = [
        'price_before_vat' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'exempt_from_vat' => 'boolean',
    ];

    public function scannedReceipt(): BelongsTo
    {
        return $this->belongsTo(ScannedReceipt::class);
    }
}
