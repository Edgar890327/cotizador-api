<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MantenimientoMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create a mantenimiento migration table
        Schema::create('mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->boolean('estado');
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
        // drop a mantenimiento migration table
        Schema::dropIfExists('mantenimiento');
    }
}
