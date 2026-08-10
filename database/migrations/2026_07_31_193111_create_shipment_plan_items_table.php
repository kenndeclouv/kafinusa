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
        Schema::create('shipment_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_plan_id')->constrained('shipment_plans')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedTinyInteger('batch_number')->default(1)->comment('Nomor muatan: 1, 2, 3, ...');
            $table->unsignedInteger('quantity')->comment('Quantity yang masuk muatan ini');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_plan_items');
    }
};
