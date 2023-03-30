<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CotizacionHistorial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizacion_historial', function (Blueprint $table) {
            $table->bigIncrements('id_cotizacion_historial');
            $table->unsignedBigInteger('cliente_id');
            $table->string('marca');
            $table->string('modelo');
            $table->string('submarca');
            $table->string('version');
            //cp
            $table->string('codigo_postal');
            $table->string('localidad');
            //emitido bool
            $table->boolean('emitido')->default(false);
            // emitido_por
            $table->string('emitido_por')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
