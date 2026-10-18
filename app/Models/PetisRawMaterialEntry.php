<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetisRawMaterialEntry extends Model
{
    protected $fillable = [
        'warehouse_id',
        'item_id',
        'date',
        'type',
        'quantity',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    // Type labels
    public const TYPE_PURCHASE = 'purchase'; // Pembelian (IN)
    public const TYPE_USAGE    = 'usage';    // Pemakaian Produksi (OUT)
    public const TYPE_SALE     = 'sale';     // Penjualan bahan (OUT)

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_PURCHASE => 'Pembelian',
            self::TYPE_USAGE    => 'Pemakaian',
            self::TYPE_SALE     => 'Penjualan',
            default             => $type,
        };
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
