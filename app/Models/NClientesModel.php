<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NClientesModel extends Model
{
    //create a model of clientes from clientes migration
    protected $table = 'clientes';
    //primary key
    protected $primaryKey = 'cliente_id';
    //fillable fields
    protected $fillable = [
        'cliente_id',
        'admin_id',
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
        'ana_descuento',
        'bloqueado'
    ];
    //hidden fields
    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];
    //relationship with admin
    public function admin()
    {
        return $this->belongsTo('App\Models\NAdminModel', 'admin_id');
    }
    //relationship with subclientes
    public function subclientes()
    {
        return $this->hasMany('App\Models\NSubClienteModel', 'cliente_id');
    }

}
