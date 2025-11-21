<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $connection = 'mysql';
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    protected $fillable = [
        'id_categoria',
        'nombre',
        'descripcion',
        'precio',
        'tiempo_preparacion',
        'imagen_url',
        'activo',
        'fecha_creacion',
        'ultima_actualizacion',
        'fecha_eliminacion'
    ];
    public $timestamps = false;

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_producto', 'id_producto', 'id_menu')
            ->withPivot('cantidad_disponible');
    }

    public function menuProductos()
    {
        return $this->hasMany(MenuProducto::class, 'id_producto', 'id_producto');
    }

    public function carrito()
    {
        return $this->belongsToMany(Carrito::class, 'carrito_producto', 'id_producto', 'id_carrito')
            ->withPivot('cantidad');
    }

    public function carritoProductos()
    {
        return $this->hasMany(CarritoProducto::class, 'id_producto', 'id_producto');
    }

    public function orden()
    {
        return $this->belongsToMany(Orden::class, 'orden_detalle', 'id_producto', 'id_orden')
            ->withPivot('cantidad');
    }

    public function ordenDetalle()
    {
        return $this->hasMany(OrdenDetalle::class, 'id_orden', 'id_producto');
    }
}
