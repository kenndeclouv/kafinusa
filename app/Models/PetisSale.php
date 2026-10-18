<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetisSale extends Model
{
    protected $fillable = [
        'warehouse_id',
        'date',
        'salesman_name',
        'muatan_kaleng',
        'muatan_refill',
        'retur_kaleng',
        'retur_refill',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'muatan_kaleng' => 'integer',
        'muatan_refill' => 'integer',
        'retur_kaleng' => 'integer',
        'retur_refill' => 'integer',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Penjualan bersih kaleng (Muatan - Retur)
     */
    public function getSoldKalengAttribute(): int
    {
        return max(0, $this->muatan_kaleng - $this->retur_kaleng);
    }

    /**
     * Penjualan bersih refill (Muatan - Retur)
     */
    public function getSoldRefillAttribute(): int
    {
        return max(0, $this->muatan_refill - $this->retur_refill);
    }
}
