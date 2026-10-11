<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel petis_raw_material_entries:
 * Mencatat PEMBELIAN, PEMAKAIAN, dan PENJUALAN bahan baku petis per item per hari.
 * 
 * type: 'purchase' (Pembelian/IN), 'usage' (Pemakaian Produksi/OUT), 'sale' (Penjualan bahan/OUT)
 * 
 * Setiap baris = satu transaksi bahan baku (satu item, satu jenis, satu tanggal).
 * Mirip dengan stock_mutations tapi khusus untuk bahan baku petis dengan label yang jelas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petis_raw_material_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['purchase', 'usage', 'sale']); // Pembelian | Pemakaian | Penjualan
            $table->decimal('quantity', 12, 3);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['warehouse_id', 'item_id', 'date', 'type'], 'petis_raw_entries_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petis_raw_material_entries');
    }
};
