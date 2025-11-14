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

    public function Categoria() {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }
}
