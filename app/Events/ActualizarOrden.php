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
    public $orden;
    public function __construct($idUsuario, $orden)
    {
        $this->idUsuario = $idUsuario;
        $this->orden = $orden;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('usuario.'.$this->idUsuario);
    }

    public function broadcastWith() {
        return [
            'ordenes' => $this->orden
        ];
    }
}
