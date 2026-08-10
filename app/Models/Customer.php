<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'market_id',
        'customer_category_id',
        'name',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function category()
    {
        return $this->belongsTo(CustomerCategory::class, 'customer_category_id');
    }

    public function orderBooks()
    {
        return $this->hasMany(OrderBook::class);
    }

    public function scopeSearch($query, $term)
    {
        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', $term)
              ->orWhereHas('market', function ($q2) use ($term) {
                  $q2->where('name', 'like', $term);
              });
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getMissedStreak($marketOrderBookIds)
    {
        $customerOrderBookIds = $this->orders->pluck('order_book_id')->toArray();
        
        $streak = 0;
        foreach ($marketOrderBookIds as $bookId) {
            if (in_array($bookId, $customerOrderBookIds)) {
                break;
            }
            $streak++;
        }
        
        return $streak;
    }
}
