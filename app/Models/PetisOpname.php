<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetisOpname extends Model
{
    protected $fillable = [
        'warehouse_id',
        'item_id',
        'date',
        'actual_stock',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'actual_stock' => 'decimal:3',
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
