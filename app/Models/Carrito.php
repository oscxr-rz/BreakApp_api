<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $connection = 'mysql';
    protected $table = 'carrito';
    protected $primaryKey = 'id_carrito';

    protected $fillable = [
        'id_usuario',
        'fecha_creacion',
        'ultima_actualizacion'
    ];

    public $timestamps = false;

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_producto', 'id_carrito', 'id_producto')
            ->withPivot('cantidad');
    }

    public function carritoProductos()
    {
        return $this->hasMany(CarritoProducto::class, 'id_menu', 'id_menu');
    }
}
