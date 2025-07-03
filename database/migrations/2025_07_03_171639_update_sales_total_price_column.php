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
        Schema::table('sales', function (Blueprint $table) {
            // Cambiar de DECIMAL(8,2) a DECIMAL(15,2) para soportar valores más grandes
            // DECIMAL(15,2) permite hasta 9,999,999,999,999.99
            $table->decimal('total_price', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Volver al formato original
            $table->decimal('total_price', 8, 2)->change();
        });
    }
};