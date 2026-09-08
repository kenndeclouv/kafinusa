<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
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

    public function reference()
    {
        return $this->morphTo();
    }
}
