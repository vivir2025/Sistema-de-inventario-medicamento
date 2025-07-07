<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreFieldsToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('serie')->nullable()->after('image');
            $table->string('riesgo')->nullable()->after('serie');
            $table->string('vida_util')->nullable()->after('riesgo');
            $table->string('registro_sanitario')->nullable()->after('vida_util');
            $table->string('presentacion_comercial')->nullable()->after('registro_sanitario');
            $table->string('forma_farmaceutica')->nullable()->after('presentacion_comercial');
            $table->string('concentracion')->nullable()->after('forma_farmaceutica');
            $table->string('unidad_medida')->nullable()->after('concentracion');
            $table->string('marca')->nullable()->after('unidad_medida');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'serie',
                'riesgo',
                'vida_util',
                'registro_sanitario',
                'presentacion_comercial',
                'forma_farmaceutica',
                'concentracion',
                'unidad_medida'
            ]);
        });
    }
}