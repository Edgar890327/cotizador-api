<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Nclientes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('cliente_id');
            $table->integer("admin_id")->unsigned();
            $table->string('nombre')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre_compania')->nullable();
            $table->string('direccion');
            //telefono
            $table->string('telefono');
            $table->string('tipo_cliente');
            $table->string('genero');

            //cod_estado
            $table->integer('cod_estado');
            $table->integer('cod_municipio');
            $table->string('estado');
            $table->string('municipio');
            $table->integer('cod_postal');

            //fecha nacimiento
            $table->string('fecha_nacimiento');
            //rfc
            $table->string('rfc');
            //fis_mor
            $table->string('fis_mor');

            //chubb_person_id
            $table->bigInteger('chubb_person_id')->nullable();
            

            $table->integer("gs_descuento");
            $table->integer("qualitas_descuento");
            $table->integer("chubb_descuento");
            $table->integer("mapfre_descuento");
            $table->integer("banorte_descuento");
            $table->integer("ana_descuento");


            $table->timestamps();

            //foreign key admin_id
            $table->foreign('admin_id')->references('admin_id')->on('admin')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
