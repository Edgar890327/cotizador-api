<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class AdminModel extends Model
{
    use Authenticatable, Authorizable, HasFactory;
    protected $table = 'admin';
    protected $primaryKey = 'admin_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_id', 'nombre', 'apellido_paterno', 'email', 'password',
        'telefono', 'token',
        'gs_descuento',
        'qualitas_descuento',
        'chubb_descuento',
        'mapfre_descuento',
        'banorte_descuento',
        'ana_descuento'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'created_at',
        'updated_at'
    ];


}
