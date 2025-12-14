<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ticket';
    protected $primaryKey = 'id_ticket';
    protected $fillable = [
        'id_orden',
        'numero_ticket',
        'pdf_url',
        'fecha_creacion',
        'ultima_actualizacion'
    ];

    public $timestamps = false;

    public function orden(){
        return $this->hasOne(Orden::class, 'id_orden', 'id_orden');
    }
}
