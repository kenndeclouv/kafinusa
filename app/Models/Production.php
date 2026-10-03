<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'item_category_id',
        'team_name',
        'date',
        'raw_item_id',
        'raw_quantity',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function rawItem()
    {
        return $this->belongsTo(Item::class, 'raw_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasMany(ProductionResult::class);
    }

    public function stockMutations()
    {
        return $this->morphMany(StockMutation::class, 'reference');
    }

    /**
     * Memproses mutasi stok untuk produksi ini.
     * Harus dipanggil setelah Production dan ProductionResult disimpan.
     */
    public function processMutations()
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            // 1. Kurangi stok bahan baku (OUT)
            $rawStock = WarehouseStock::firstOrCreate(
                ['warehouse_id' => $this->warehouse_id, 'item_id' => $this->raw_item_id],
                ['current_stock' => 0, 'physical_stock' => 0]
            );
            $rawStock = WarehouseStock::where('id', $rawStock->id)->lockForUpdate()->first();
            $rawStock->current_stock -= $this->raw_quantity;
            $rawStock->physical_stock -= $this->raw_quantity;
            $rawStock->save();

            StockMutation::create([
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $this->raw_item_id,
                'type' => 'out',
                'quantity' => $this->raw_quantity,
                'reference_type' => self::class,
                'reference_id' => $this->id,
                'user_id' => $this->user_id,
                'mutation_date' => $this->date,
                'sender_name' => 'Produksi Harian',
                'transaction_category' => 'Bahan Baku',
                'notes' => 'Bahan baku produksi' . ($this->notes ? ': ' . $this->notes : ''),
            ]);

            // 2. Tambah stok barang jadi (IN)
            foreach ($this->results as $result) {
                $finishedStock = WarehouseStock::firstOrCreate(
                    ['warehouse_id' => $this->warehouse_id, 'item_id' => $result->item_id],
                    ['current_stock' => 0, 'physical_stock' => 0]
                );
                $finishedStock = WarehouseStock::where('id', $finishedStock->id)->lockForUpdate()->first();
                $finishedStock->current_stock += $result->quantity;
                $finishedStock->physical_stock += $result->quantity;
                $finishedStock->save();

                StockMutation::create([
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $result->item_id,
                    'type' => 'in',
                    'quantity' => $result->quantity,
                    'reference_type' => self::class,
                    'reference_id' => $this->id,
                    'user_id' => $this->user_id,
                    'mutation_date' => $this->date,
                    'sender_name' => 'Produksi Harian',
                    'transaction_category' => 'Barang Jadi',
                    'notes' => 'Hasil produksi: ' . $this->notes,
                ]);
            }
        });
    }
}
