<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel petis_productions:
 * Mencatat hasil produksi petis harian per gudang.
 * - result_kaleng: total petis kaleng (KI) yg dihasilkan hari itu
 * - result_refill: total petis refill/bal (Rf) yg dihasilkan hari itu
 * - result_sisa_kg: sisa adonan petis dalam Kg
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petis_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('date');
            $table->integer('result_kaleng')->default(0); // pcs kaleng
            $table->integer('result_refill')->default(0); // pcs bal/refill
            $table->decimal('result_sisa_kg', 10, 3)->default(0); // sisa adonan dalam kg
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['warehouse_id', 'date'], 'petis_productions_daily_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petis_productions');
    }
};
