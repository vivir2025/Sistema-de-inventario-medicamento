<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySalesTableForMunicipalities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
           
            
            // Agregar nuevas columnas para el sistema de municipios
            $table->enum('origin_municipality', ['cajibio', 'morales', 'piendamo'])->after('product_id')->comment('Municipio de donde sale el producto');
            $table->enum('destination_municipality', ['cajibio', 'morales', 'piendamo'])->nullable()->after('origin_municipality')->comment('Municipio destino (solo para transferencias)');
            $table->enum('sale_type', ['sale', 'transfer'])->default('sale')->after('destination_municipality')->comment('Tipo: venta o transferencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['origin_municipality', 'destination_municipality', 'sale_type']);
        });
    }
}
