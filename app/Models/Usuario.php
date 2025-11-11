<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $database = 'mysql';
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'email_verificacion',
        'telefono',
        'password',
        'tipo',
        'grupo',
        'grupo',
        'imagen_url',
        'activo',
        'fecha_registro',
        'ultima_actualizacion',
        'fecha_eliminacion'
    ];
    protected $hidden = [
        'password'
    ];

    public $timestamps = false;

    protected function casts()
        {
            return[
                'email_verificacion' => 'datetime',
                'password' => 'hashed',
            ];
        }
}
