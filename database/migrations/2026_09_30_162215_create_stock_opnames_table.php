<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('month');
            $table->year('year');
            $table->decimal('system_stock', 15, 2)->default(0)->comment('Stok sebelum di-opname (dalam Satuan Item atau Kg)');
            $table->decimal('actual_stock', 15, 2)->default(0)->comment('Stok asli hasil hitungan fisik (dalam Satuan Item atau Kg)');
            $table->decimal('difference', 15, 2)->default(0)->comment('Selisih (actual - system)');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Ensure unique opname per warehouse, item, month, and year
            $table->unique(['warehouse_id', 'item_id', 'month', 'year'], 'unique_opname_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
