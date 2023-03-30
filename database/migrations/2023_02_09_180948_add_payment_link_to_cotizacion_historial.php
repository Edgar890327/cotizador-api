<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentLinkToCotizacionHistorial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotizacion_historial', function (Blueprint $table) {
            // add payment link
            $table->string('payment_link')->nullable()->default("vacio");
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
            // remove payment link
            $table->dropColumn('payment_link');
        });
    }
}
