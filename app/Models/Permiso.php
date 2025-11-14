<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $connection = 'mysql';
    protected $table = 'Permiso';
    protected $primaryKey = 'id_permiso';
    protected $fillable = [
        'nombre_permiso',
        'descripcion',
        'activo',
        'fecha_creacion',
        'ultima_actualizacion',
        'fecha_eliminacion'
    ];

    public $timestamps = false;
}
