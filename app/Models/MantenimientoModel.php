<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MantenimientoModel extends Model
{
    // define the table name
    protected $table = 'mantenimiento';
    // define the primary key
    protected $primaryKey = 'id';
    // define the fillable columns
    protected $fillable = [
        'estado',
        'provider',
        'descuento',
    ];
    // define the hidden columns
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
