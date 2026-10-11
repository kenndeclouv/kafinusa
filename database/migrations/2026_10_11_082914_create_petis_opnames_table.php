<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel petis_opnames:
 * Mencatat stok opname (stok nyata fisik) bahan baku petis per item per tanggal.
 * Diisi manual oleh checker/admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petis_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('actual_stock', 12, 3); // stok fisik aktual
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Unique: satu item, satu gudang, satu tanggal (bisa di-update, tidak duplikat)
            $table->unique(['warehouse_id', 'item_id', 'date'], 'petis_opnames_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petis_opnames');
    }
};
