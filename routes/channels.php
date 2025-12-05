<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('menu', function () {
    return true;
});

Broadcast::channel('admin', function () {
    return true;
});

Broadcast::channel('usuario.{id}', function ($usuario, $id) {
    return (int) $usuario->id_usuario === (int) $id;
});
