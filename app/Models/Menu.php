<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $connection = 'mysql';
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    protected $fillable = [
        'fecha',
        'activo',
        'fecha_creacion',
        'ultima_actualizacion',
        'fecha_eliminacion'
    ];
    public $timestamps = false;

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'menu_producto', 'id_menu', 'id_producto')
            ->withPivot('cantidad_disponible');
    }

    public function menuProductos()
    {
        return $this->hasMany(MenuProducto::class, 'id_menu', 'id_menu');
    }
}
