<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Ncapacitacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('capacitacion', function (Blueprint $table) {
            $table->increments('capacitacion_id');
            $table->integer("empleado_id")->unsigned();
            $table->integer("curso_id")->unsigned();
            $table->double('progreso');
            $table->timestamps();

            //foreign keys
            $table->foreign("empleado_id")->references("empleado_id")->on("empleados");
            $table->foreign("curso_id")->references("curso_id")->on("cursos");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('capacitacion');
    }
}
