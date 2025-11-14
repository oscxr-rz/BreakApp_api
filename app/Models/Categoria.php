<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $connection = 'mysql';
    protected $table = 'categoria';
    protected $primaryKey = 'id_categoria';
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'fecha_creacion',
        'ultima_actualizacion',
        'fecha_eliminacion'
    ];
    public $timestamps = false;

    public function Productos() {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }
}
