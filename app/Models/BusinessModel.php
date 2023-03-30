<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class BusinessModel extends Model
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;
    protected $table = 'business';
    protected $primaryKey = 'business_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id', 'admin_id', 'name', 'last_name',
        'middle_name', 'email', 'password', 'phone', 'photo',
        'state', 'business_name', 'address', 'discount',
        'access', 'token'
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
