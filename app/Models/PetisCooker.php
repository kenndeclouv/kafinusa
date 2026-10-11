<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PetisCooker extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'warehouse_id', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
