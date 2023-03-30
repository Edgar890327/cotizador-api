<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Ramsey\Uuid\v1;

class AddCotizacionIdAHistorial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotizacion_historial', function (Blueprint $table) {
            $table->string("folio_gs")->default('000000')->nullable();
            $table->string("folio_mapfre")->default('000000')->nullable();
            $table->string("folio_banorte")->default('000000')->nullable();
            $table->string("folio_chubb")->default('000000')->nullable();
            $table->string("folio_ana")->default('000000')->nullable();
            $table->string("folio_qualitas")->default('000000')->nullable();
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
            $table->dropColumn("folio_gs");
            $table->dropColumn("folio_mapfre");
            $table->dropColumn("folio_banorte");
            $table->dropColumn("folio_chubb");
            $table->dropColumn("folio_ana");
            $table->dropColumn("folio_qualitas");
        });
    }
}
