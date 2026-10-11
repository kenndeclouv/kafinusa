<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel petis_sales:
 * Mencatat muatan dan retur petis per sales/driver per hari.
 * - muatan_kaleng: muatan kaleng (KI) yg dibawa salesman
 * - muatan_refill: muatan refill (Rf) yg dibawa salesman
 * - retur_kaleng: sisa kaleng yg kembali ke gudang
 * - retur_refill: sisa refill yg kembali ke gudang
 * Penjualan bersih (GT) = muatan - retur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petis_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('date');
            $table->string('salesman_name'); // Nama salesman / driver (e.g. Ikhsan, Sutekno, dll)
            $table->integer('muatan_kaleng')->default(0);
            $table->integer('muatan_refill')->default(0);
            $table->integer('retur_kaleng')->default(0);
            $table->integer('retur_refill')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['warehouse_id', 'date', 'salesman_name'], 'petis_sales_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petis_sales');
    }
};
