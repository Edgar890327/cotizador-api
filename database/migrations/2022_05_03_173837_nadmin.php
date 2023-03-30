<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Nadmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->increments('admin_id');
            $table->string("nombre",25);
            $table->string("apellido_paterno",40);
            $table->string("email",35);
            $table->string("password",80);
            $table->string("telefono",10);
            $table->string("token",60);
            $table->integer("gs_descuento");
            $table->integer("qualitas_descuento");
            $table->integer("chubb_descuento");
            $table->integer("mapfre_descuento");
            $table->integer("banorte_descuento");
            $table->integer("ana_descuento");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin');
    }
}
