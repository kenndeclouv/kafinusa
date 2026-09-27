<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentBatch extends Model
{
    protected $fillable = [
        'shipment_plan_id',
        'batch_number',
        'shipment_date',
        'status',
        'warehouse_id',
    ];

    protected $casts = [
        'shipment_date' => 'date',
    ];

    public function shipmentPlan()
    {
        return $this->belongsTo(ShipmentPlan::class);
    }
}