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
        Schema::table('shipment_plan_items', function (Blueprint $table) {
            $table->integer('return_quantity')->default(0)->after('quantity');
            $table->boolean('is_checked_g')->default(false)->after('return_quantity');
            $table->boolean('is_checked_s')->default(false)->after('is_checked_g');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_plan_items', function (Blueprint $table) {
            $table->dropColumn(['return_quantity', 'is_checked_g', 'is_checked_s']);
        });
    }
};
