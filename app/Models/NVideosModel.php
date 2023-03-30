<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NVideosModel extends Model
{
    //create a model from the table videos
    protected $table = 'videos';
    protected $primaryKey = 'video_id';
    protected $fillable = [
        'video_id',
        'curso_id', 
        'nombre',
        'descripcion',  
        'url',
        'created_at',
        'updated_at',
    ];

}
