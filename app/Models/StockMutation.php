<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'user_id',
        'mutation_date',
        'sender_name',
        'transaction_category',
        'notes',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function getBalanceAfterAttribute()
    {
        // Compute running balance based on all mutations for this item and warehouse up to this mutation
        return self::where('item_id', $this->item_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->where(function($q) {
                $q->where('created_at', '<', $this->created_at)
                  ->orWhere(function($q2) {
                      $q2->where('created_at', $this->created_at)
                         ->where('id', '<=', $this->id);
                  });
            })
            ->selectRaw('SUM(CASE WHEN type = "in" THEN quantity ELSE -quantity END) as balance')
            ->value('balance') ?? 0;
    }
}
