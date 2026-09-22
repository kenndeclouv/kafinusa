<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'item_id',
        'quantity',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
