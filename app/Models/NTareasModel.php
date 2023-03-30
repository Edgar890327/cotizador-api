<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NTareasModel extends Model
{
    //create a model from the table tareas
    protected $table = 'tareas';
    //primary key
    protected $primaryKey = 'tarea_id';
    protected $fillable = [
        'empleado_id', 
        'titulo', 
        'descripcion', 
        'estado', 
        'fecha_inicio', 
        'fecha_entrega', 
        'prioridad'
    ];
}
