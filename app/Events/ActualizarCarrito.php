<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActualizarCarrito implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $idUsuario;
    public $carrito;

    public function __construct($idUsuario, $carrito)
    {
        $this->idUsuario = $idUsuario;
        $this->carrito = $carrito;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('usuario.'.$this->idUsuario);
    }

    public function broadcastWith() {
        return [
            'carrito' => $this->carrito
        ];
    }
}
