<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Broadcast;

class ActualizarEstadoOrden implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $idUsuario;
    public $idOrden;
    public $estado;
    public function __construct($idUsuario, $idOrden, $estado)
    {
        $this->idUsuario = $idUsuario;
        $this->idOrden = $idOrden;
        $this->estado = $estado;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('usuario.'.$this->idUsuario);
    }

    public function broadcastWith() {
        return [
            'idOrden' => $this->idOrden,
            'estado' => $this->estado
        ];
    }
}
