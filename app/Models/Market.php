<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    /** @use HasFactory<\Database\Factories\MarketFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeSearch($query, $term)
    {
        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', $term)
              ->orWhere('name', 'like', $term)
              ->orWhere('address', 'like', $term);
        });
    }
    public function salesSchedules()
    {
        return $this->hasMany(SalesSchedule::class);
    }
}
