<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_id_number',
        'name',
        'phone_number',
        'position',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeSearch($query, $term)
    {
        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('employee_id_number', 'like', $term)
              ->orWhere('name', 'like', $term)
              ->orWhere('phone_number', 'like', $term)
              ->orWhere('position', 'like', $term);
        });
    }
    public function salesSchedules()
    {
        return $this->hasMany(SalesSchedule::class);
    }
}
