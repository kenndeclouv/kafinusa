<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('petis_raw_material_entries')) {
            DB::statement("UPDATE petis_raw_material_entries SET date = substr(date, 1, 10) WHERE length(date) > 10");
        }

        if (Schema::hasTable('petis_opnames')) {
            DB::statement("UPDATE petis_opnames SET date = substr(date, 1, 10) WHERE length(date) > 10");
        }

        if (Schema::hasTable('petis_productions')) {
            DB::statement("UPDATE petis_productions SET date = substr(date, 1, 10) WHERE length(date) > 10");
        }

        if (Schema::hasTable('petis_sales')) {
            DB::statement("UPDATE petis_sales SET date = substr(date, 1, 10) WHERE length(date) > 10");
        }
    }

    public function down(): void
    {
        // No-op
    }
};
