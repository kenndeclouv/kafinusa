<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BustsDashboardCache;

class WarehouseStock extends Model
{
    use HasFactory, BustsDashboardCache;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'current_stock',
        'physical_stock',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
