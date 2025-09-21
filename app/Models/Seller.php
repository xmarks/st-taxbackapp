<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $fillable = [
        'id_num',
        'id_type',
        'name',
        'address',
        'town',
        'country',
    ];

    public function scannedReceipts(): HasMany
    {
        return $this->hasMany(ScannedReceipt::class);
    }

    public static function findOrCreateBySeller(array $sellerData): self
    {
        return self::firstOrCreate(
            ['id_num' => $sellerData['idNum']],
            [
                'id_type' => $sellerData['idType'] ?? 'NUIS',
                'name' => $sellerData['name'],
                'address' => $sellerData['address'] ?? null,
                'town' => $sellerData['town'] ?? null,
                'country' => $sellerData['country'] ?? 'ALB',
            ]
        );
    }

    public function isInAlbania(): bool
    {
        return $this->country === 'ALB';
    }
}
