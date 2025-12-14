<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $connection = 'mysql';
    protected $table = 'notificacion';
    protected $primaryKey = 'id_notificacion';
    protected $fillable = [
        'id_usuario',
        'id_orden',
        'id_promocion',
        'tipo',
        'titulo',
        'mensaje',
        'canal',
        'leido',
        'oculto',
        'fecha_creacion',
        'ultima_actualizacion'
    ];

    public $timestamps = false;
}
