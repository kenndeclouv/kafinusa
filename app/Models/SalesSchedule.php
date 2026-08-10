<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'market_id',
        'day_of_week',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}
