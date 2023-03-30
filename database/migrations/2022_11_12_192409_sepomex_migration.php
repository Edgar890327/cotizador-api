<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SepomexMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create a schema for the table 'sepomex'
        Schema::create('sepomex', function (Blueprint $table) {
            $table->string('d_codigo', 100);
            $table->string('d_asenta', 100);
            $table->string('d_tipo_asenta', 100);
            $table->string('D_mnpio', 100);
            $table->string('d_estado', 100);
            $table->string('d_ciudad', 100);
            $table->string('d_CP', 100);
            $table->string('c_estado', 100);
            $table->string('c_oficina', 100);
            $table->string('c_CP', 100);
            $table->string('c_tipo_asenta', 100);
            $table->string('c_mnpio', 100);
            $table->string('id_asenta_cpcons', 100);
            $table->string('d_zona', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop the table 'sepomex'
        Schema::dropIfExists('sepomex');
    }
}
