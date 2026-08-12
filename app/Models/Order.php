<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_book_id',
        'customer_id',
        'status',
        'price_type',
        'total_calculated_weight',
    ];

    public function orderBook()
    {
        return $this->belongsTo(OrderBook::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
