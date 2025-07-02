<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMunicipalitiesToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
           $table->enum('origin_municipality', ['cajibio', 'morales', 'piendamo'])
          ->after('customer_id');
           $table->enum('destination_municipality', ['cajibio', 'morales', 'piendamo'])
          ->after('origin_municipality');
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
           $table->dropColumn('origin_municipality');
        $table->dropColumn('destination_municipality');
    });
    }
}
