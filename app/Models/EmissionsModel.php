<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class EmissionsModel extends Model
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;
    protected $table = 'emissions';
    protected $primaryKey = 'emission_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'emission_id', 'quote_id', 'document_1', 'document_2',
        'document_3', 'prima_total'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at', 'deleted_at', 'verified'
    ];

}
