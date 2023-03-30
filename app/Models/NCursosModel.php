<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NCursosModel extends Model
{
    //model of cursos migration
    protected $table = 'cursos';
    protected $primaryKey = 'curso_id';
    protected $fillable = [
        'nombre',
        'descripcion',
        'objetivo',
        'categoria',
        'created_at',
        'updated_at'
    ];
}
