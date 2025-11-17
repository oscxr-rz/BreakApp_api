<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarjetaLocal extends Model
{
    protected $connection = 'mysql';
    protected $table = 'tarjeta_local';
    protected $primaryKey = 'id_tarjeta_local';
    protected $fillable = [
        'id_usuario',
        'saldo',
        'fecha_creacion',
        'ultima_actualizacion'
    ];

    public $timestamps = false;

    public function usuario() {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
