<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuProducto extends Model
{
    protected $connection = 'mysql';
    protected $table = 'menu_producto';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'id_menu',
        'id_producto',
        'cantidad_disponible'
    ];

    public $timestamps = false;

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu', 'id_menu');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
