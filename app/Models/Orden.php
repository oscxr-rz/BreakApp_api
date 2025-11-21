<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $connection = 'mysql';
    protected $table = 'orden';
    protected $primaryKey = 'id_orden';
    protected $fillable = [
        'id_usuario',
        'codigo_qr',
        'estado',
        'total',
        'metodo_pago',
        'imagen_url',
        'oculto',
        'hora_recogida',
        'fecha_creacion',
        'ultima_actualizacion'
    ];

    public $timestamps = false;

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_detalle', 'id_orden', 'id_producto')
            ->withPivot('cantidad', 'precio_unitario', 'notas');
    }

    public function ordenDetalle()
    {
        return $this->hasMany(OrdenDetalle::class, 'id_orden', 'id_orden');
    }
}
