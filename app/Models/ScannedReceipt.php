<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScannedReceipt extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'fiscal_id',
        'iic',
        'fic',
        'invoice_number',
        'invoice_order_number',
        'business_unit',
        'cash_register',
        'tcr_code',
        'operator_code',
        'software_code',
        'receipt_created_at',
        'scanned_at',
        'total_price',
        'total_price_without_vat',
        'total_vat_amount',
        'tax_free_amount',
        'invoice_type',
        'invoice_version',
        'is_einvoice',
        'simplified_invoice',
        'original_url',
        'url_price',
        'raw_api_response',
    ];

    protected $casts = [
        'receipt_created_at' => 'datetime',
        'scanned_at' => 'datetime',
        'total_price' => 'decimal:2',
        'total_price_without_vat' => 'decimal:2',
        'total_vat_amount' => 'decimal:2',
        'tax_free_amount' => 'decimal:2',
        'url_price' => 'decimal:2',
        'is_einvoice' => 'boolean',
        'simplified_invoice' => 'boolean',
        'raw_api_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function taxSummaries(): HasMany
    {
        return $this->hasMany(ReceiptTaxSummary::class);
    }

    public function isValidForScanning(): bool
    {
        $validityHours = (int) config('receipt.validity_hours', 24);
        $expiryTime = $this->receipt_created_at->addHours($validityHours);

        return Carbon::now()->lte($expiryTime);
    }

    public function wasScannedInTime(): bool
    {
        $validityHours = (int) config('receipt.validity_hours', 24);
        $expiryTime = $this->receipt_created_at->addHours($validityHours);

        return $this->scanned_at->lte($expiryTime);
    }

    public function getVatPercentage(): float
    {
        if ($this->total_price_without_vat == 0) {
            return 0;
        }

        return ($this->total_vat_amount / $this->total_price_without_vat) * 100;
    }

    public static function existsByIic(string $iic): bool
    {
        return self::where('iic', $iic)->exists();
    }

    public static function userAlreadyScanned(int $userId, string $iic): bool
    {
        return self::where('user_id', $userId)
                  ->where('iic', $iic)
                  ->exists();
    }
}
