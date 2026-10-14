<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetisRawMaterialInitialStock extends Model
{
    protected $fillable = [
        'warehouse_id',
        'item_id',
        'month',
        'initial_stock',
        'user_id',
    ];

    protected $casts = [
        'initial_stock' => 'decimal:3',
    ];

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
