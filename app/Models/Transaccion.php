<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    protected $connection = 'mysql';
    protected $table = 'transaccion';
    protected $primaryKey = 'id_transaccion';
    protected $fillable = [
        'id_usuario',
        'id_orden',
        'monto',
        'tipo',
        'referencia',
        'fecha_creacion'
    ];
    public $timestamps = false;
}
