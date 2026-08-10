<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderBook extends Model
{
    protected $fillable = [
        'market_id',
        'employee_id',
        'book_date',
        'status',
    ];

    protected $casts = [
        'book_date' => 'date',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function shipmentPlan()
    {
        return $this->hasOne(ShipmentPlan::class);
    }
}
