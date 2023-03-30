<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
class QuotesModel extends Model
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;
    protected $table = 'quotes';
    protected $primaryKey = 'quote_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote_id', 'client_id', 'brand', 'sub_brand',
        'model', 'description'
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
