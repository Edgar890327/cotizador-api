<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class ClientsModel extends Model
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;
    protected $table = 'clients';
    protected $primaryKey = 'client_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'business_id', 'name', 'last_name',
        'middle_name', 'email', 'password','phone', 'fis_mor',
        'rfc', 'clave_elector', 'curp', 'gender',
        'edo_civil', 'street', 'num', 'postal_code',
        'colony', 'born', 'website', 'token','chubb_person_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'created_at',
        'updated_at', 'deleted_at', 'verified'
    ];
}
