<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Nempleados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->increments('empleado_id');
            $table->integer("admin_id")->unsigned();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('direccion');
            $table->string('telefono');
            $table->string('email')->unique();  
            $table->string('password');
            $table->string('rfc')->unique()->nullable();
            $table->string('genero');
            $table->string('sucursal');
            $table->timestamps();

            //foreign key admin_id
            $table->foreign('admin_id')->references('admin_id')->on('admin');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empleados');
    }
}
