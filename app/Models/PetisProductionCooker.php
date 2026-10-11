<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetisProductionCooker extends Model
{
    protected $fillable = [
        'petis_production_id',
        'cooker_id',
        'cooker_name',
        'sessions',
    ];

    protected $casts = [
        'sessions' => 'decimal:2',
    ];

    public function production()
    {
        return $this->belongsTo(PetisProduction::class, 'petis_production_id');
    }

    public function cooker()
    {
        return $this->belongsTo(PetisCooker::class, 'cooker_id');
    }
}
