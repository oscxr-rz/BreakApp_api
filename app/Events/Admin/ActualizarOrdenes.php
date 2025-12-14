<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActualizarOrdenes implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $idOrden;
    public function __construct(int $idOrden)
    {
        $this->idOrden = $idOrden;
    }

    public function broadcastOn()
    {
        return new Channel('admin');
    }

    public function broadcastAs() {
        return 'ActualizarOrdenes';
    }

    public function broadcastWith()
    {
        return [
            'idOrden' => $this->idOrden
        ];
    }
}
