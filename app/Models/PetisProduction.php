<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetisProduction extends Model
{
    protected $fillable = [
        'warehouse_id',
        'date',
        'result_kaleng',
        'result_refill',
        'result_sisa_kg',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'result_kaleng' => 'integer',
        'result_refill' => 'integer',
        'result_sisa_kg' => 'decimal:3',
    ];

    /**
     * Berat 1 kaleng petis dalam kg (1 KI = 9.3 kg)
     */
    public const WEIGHT_KALENG_KG = 9.3;

    /**
     * Berat 1 refill petis dalam kg (1 Rf = 5.0 kg)
     */
    public const WEIGHT_REFILL_KG = 5.0;

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cookers()
    {
        return $this->hasMany(PetisProductionCooker::class, 'petis_production_id');
    }

    /**
     * Total masakan (sesi) seluruh koki hari ini
     */
    public function getTotalSessionsAttribute(): float
    {
        return (float) $this->cookers->sum('sessions');
    }

    /**
     * Total produksi dalam Kg
     */
    public function getTotalKgAttribute(): float
    {
        return ($this->result_kaleng * self::WEIGHT_KALENG_KG)
            + ($this->result_refill * self::WEIGHT_REFILL_KG)
            + (float) $this->result_sisa_kg;
    }

    /**
     * Rata-rata per masakan (kg/mskn)
     */
    public function getAvgKgPerSessionAttribute(): float
    {
        $sessions = $this->total_sessions;
        return $sessions > 0 ? round($this->total_kg / $sessions, 2) : 0;
    }
}
