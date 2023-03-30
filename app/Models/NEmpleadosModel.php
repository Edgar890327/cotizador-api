<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NEmpleadosModel extends Model
{
    //model of empleados migration
    use Authenticatable, Authorizable, HasFactory;
    protected $table = 'empleados';
    protected $primaryKey = 'empleado_id';
    protected $fillable = [
        'admin_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'direccion',
        'telefono',
        'email',
        'password',
        'rfc',
        'genero',
        'sucursal',
    ];

    //hidden fields
    protected $hidden = [
        'password',
    ];
}
