<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaAndPolizaIdToCotizacionHistorial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotizacion_historial', function (Blueprint $table) {
            $table->double('prima')->nullable();
            $table->bigInteger('poliza_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotizacion_historial', function (Blueprint $table) {
            $table->dropColumn('prima');
            $table->dropColumn('poliza_id');
        });
    }
}
