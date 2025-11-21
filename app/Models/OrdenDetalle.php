<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenDetalle extends Model
{
    protected $connection = 'mysql';
    protected $table = 'orden_detalle';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'id_orden',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'notas'
    ];

    public $timestamps = false;

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'id_orden', 'id_orden');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
