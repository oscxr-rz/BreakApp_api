<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('menu', function () {
    return true;
});

Broadcast::channel('admin', function ($usuario) {
    return $usuario->tipo === 'ADMINISTRADOR';
});

Broadcast::channel('usuario.{id}', function ($usuario, $id) {
    return (int) $usuario->id_usuario === (int) $id;
});
