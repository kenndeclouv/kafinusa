<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel petis_production_cookers:
 * Mencatat jumlah sesi/wajan masakan per koki pada hari tertentu.
 * - cooker_name disimpan sebagai string agar history tetap abadi
 * - cooker_id nullable agar jika koki dihapus, foreign key tidak merusak riwayat
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petis_production_cookers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petis_production_id')->constrained('petis_productions')->cascadeOnDelete();
            $table->unsignedBigInteger('cooker_id')->nullable();
            $table->string('cooker_name');
            $table->decimal('sessions', 8, 2)->default(0); // misal 5.00 masakan
            $table->timestamps();

            $table->unique(['petis_production_id', 'cooker_name'], 'petis_prod_cooker_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petis_production_cookers');
    }
};
