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
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->date('mutation_date')->nullable()->after('user_id');
            $table->string('sender_name')->nullable()->after('mutation_date');
            $table->string('transaction_category')->nullable()->after('sender_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropColumn(['mutation_date', 'sender_name', 'transaction_category']);
        });
    }
};
