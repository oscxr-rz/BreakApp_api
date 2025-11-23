<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActualizarOrden implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $idUsuario;
    public $ordenes;
    public function __construct($idUsuario, $ordenes)
    {
        $this->idUsuario = $idUsuario;
        $this->ordenes = $ordenes;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('usuario.'.$this->idUsuario);
    }

    public function broadcastWith() {
        return [
            'ordenes' => $this->ordenes
        ];
    }
}
