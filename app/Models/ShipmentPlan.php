<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentPlan extends Model
{
    protected $fillable = [
        'order_book_id',
        'created_by',
        'total_batches',
    ];

    public function orderBook()
    {
        return $this->belongsTo(OrderBook::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(ShipmentPlanItem::class);
    }
}
