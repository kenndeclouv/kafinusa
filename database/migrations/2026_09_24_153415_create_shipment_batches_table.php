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
        Schema::create('shipment_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_plan_id')->constrained('shipment_plans')->cascadeOnDelete();
            $table->unsignedTinyInteger('batch_number');
            $table->date('shipment_date')->nullable();
            $table->string('status')->default('pending'); // pending, shipped
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_batches');
    }
};
