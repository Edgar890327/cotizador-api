<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Nvideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->increments('video_id');
            $table->integer("curso_id")->unsigned();
            $table->string('nombre');
            $table->string('descripcion');
            $table->string('url');
            $table->timestamps();

            //foreign key curso_id
            $table->foreign('curso_id')->references('curso_id')->on('cursos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
