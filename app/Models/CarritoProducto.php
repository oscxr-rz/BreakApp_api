<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarritoProducto extends Model
{
    protected $connection = 'mysql';
    protected $table = 'carrito_producto';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'id_carrito',
        'id_producto',
        'cantidad'
    ];

    public $timestamps = false;

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
