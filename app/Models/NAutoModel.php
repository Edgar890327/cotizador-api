<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NAutoModel extends Model
{
    //create a auto model from migration nautos
    protected $table = 'autos';
    //primary key
    protected $primaryKey = 'auto_id';
    //fillable fields
    protected $fillable = [
        'cliente_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'email',
        'rfc',
        'genero',
        'curp',
        'fecha_nacimiento',
        'edo_civil',
        'fis_mor',
        'razon_social',
        'nombre_comercial',
        'codigo_postal',
        'estado',
        'municipio',
        'direccion',
        'clave_estado',
        'clave_municipio',
        'numero_pasajeros',
        'marca',
        'submarca',
        'modelo',
        'descripcion',
        'placa',
        'motor',
        'serie',
        'id_polisa',
        'provedor',
        'prima',
        'pago',
        'link_pago',
        'link_polisa',
        'fecha_vencimiento',
        'benefi_cod_provincia',
        'benefi_cod_estado',
        'benefi_rfc',
        'benefi_telefono1',
        'benefi_correo',
        'benefi_telefono2',
        'benefi_cod_postal',
        'benefi_fecha_nacimiento',
        'benefi_sexo',
        'benefi_direccion1',
        'benefi_direccion2',
        'benefi_nombre',
        'benefi_apellido_p',
        'benefi_apellido_m',
        'benefi_tipo_persona',
        'mapfre_marca_code',
        'mapfre_modelo_code',
        'created_at',
        'updated_at'
    ];
}
