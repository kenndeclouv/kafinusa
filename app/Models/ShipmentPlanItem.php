<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentPlanItem extends Model
{
    protected $fillable = [
        'shipment_plan_id',
        'order_item_id',
        'batch_number',
        'quantity',
    ];

    public function shipmentPlan()
    {
        return $this->belongsTo(ShipmentPlan::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
