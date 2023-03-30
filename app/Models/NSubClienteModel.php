<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NSubClienteModel extends Model
{
    //
    protected $table = 'nsubcliente';
    protected $primaryKey = 'subcliente_id';
    protected $fillable = [
        'admin_id',
        'cliente_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'nombre_compania',
        'direccion',
        'telefono',
        'tipo_cliente',
        'genero',
        'cod_estado',
        'cod_municipio',
        'estado',
        'municipio',
        'cod_postal',
        'fecha_nacimiento',
        'rfc',
        'fis_mor',
        'chubb_person_id',
        'gs_descuento',
        'qualitas_descuento',
        'chubb_descuento',
        'mapfre_descuento',
        'banorte_descuento',
        'ana_descuento'
    ];
    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];
}
