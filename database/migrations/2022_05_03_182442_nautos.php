<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Nautos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autos', function (Blueprint $table) {
            $table->increments('auto_id');
            //cliente_id integer
            $table->integer("cliente_id")->unsigned();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('telefono');
            $table->string('email');  
            $table->string('rfc')->nullable();
            $table->string('genero');

            //curp
            $table->string('curp')->nullable();
            //fecha_nacimiento
            $table->string('fecha_nacimiento')->nullable();
            $table->string('edo_civil')->nullable();
            //persona
            $table->string("fis_mor");
            $table->string('razon_social')->nullable();
            $table->string('nombre_comercial')->nullable();
            
            $table->string('codigo_postal');
            $table->string('estado');
            $table->string('municipio');
            $table->string('direccion');
            $table->string('clave_estado');
            $table->string('clave_municipio');
            $table->string('numero_pasajeros');
            $table->string('marca');
            $table->string('submarca')->nullable();
            //descripcion_marca 
            $table->string('descripcion')->nullable();
            $table->string('modelo');
            $table->string('placa');
            $table->string('motor');
            $table->string('serie');
            $table->string('id_polisa')->nullable();
            $table->string('provedor')->nullable();
            $table->string('prima')->nullable();
            $table->boolean('pago')->default(false);
            $table->string('link_pago')->nullable();
            $table->string('link_polisa')->nullable();
            $table->string('fecha_vencimiento')->nullable();
            $table->string('benefi_cod_provincia')->nullable();
            $table->string('benefi_cod_estado')->nullable();
            $table->string('benefi_rfc')->nullable();
            $table->string('benefi_telefono1')->nullable();
            $table->string('benefi_correo')->nullable();
            $table->string('benefi_telefono2')->nullable();
            $table->string('benefi_cod_postal')->nullable();
            $table->string('benefi_fecha_nacimiento')->nullable();
            $table->string('benefi_sexo')->nullable();
            $table->string('benefi_direccion1')->nullable();
            $table->string('benefi_direccion2')->nullable();
            $table->string('benefi_nombre')->nullable();
            $table->string('benefi_apellido_p')->nullable();
            $table->string('benefi_apellido_m')->nullable();
            $table->string('benefi_tipo_persona')->nullable();
            $table->string('benefi_nombre_comercial')->nullable();
            $table->integer('mapfre_marca_code')->nullable();
            $table->integer('mapfre_modelo_code')->nullable();
            $table->timestamps();

            //foreign key cliente_id
            $table->foreign('cliente_id')->references('cliente_id')->on('clientes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autos');
    }
}
