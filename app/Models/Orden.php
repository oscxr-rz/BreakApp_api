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
        'nombre',
        'codigo_qr',
        'estado',
        'total',
        'metodo_pago',
        'imagen_url',
        'pagado',
        'oculto',
        'hora_recogida',
        'vencido',
        'fecha_vencimiento',
        'fecha_creacion',
        'ultima_actualizacion'
    ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_detalle', 'id_orden', 'id_producto')
            ->withPivot('cantidad', 'precio_unitario', 'notas');
    }

    public function ordenDetalle()
    {
        return $this->hasMany(OrdenDetalle::class, 'id_orden', 'id_orden');
    }

    public function ticket(){
        return $this->belongsTo(Ticket::class, 'id_orden', 'id_orden');
    }
}
