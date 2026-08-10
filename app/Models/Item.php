<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_category_id',
        'code',
        'name',
        'weight',
        'photo',
        'prices',
    ];

    protected $casts = [
        'prices' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeSearch($query, $term)
    {
        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('code', 'like', $term)
              ->orWhere('name', 'like', $term)
              ->orWhereHas('category', function ($q2) use ($term) {
                  $q2->where('name', 'like', $term);
              });
        });
    }
}
