<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }
}
