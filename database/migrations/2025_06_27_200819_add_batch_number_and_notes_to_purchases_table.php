<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchNumberAndNotesToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
          $table->string('batch_number')->unique()->after('id');
            $table->text('notes')->nullable()->after('image'); // Para notas adicionales del lote
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
             $table->dropColumn(['batch_number', 'notes']);
        });
    }
}
