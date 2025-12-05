<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActualizarProducto implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $producto;
    public function __construct($producto)
    {
        $this->producto = $producto;
    }

    public function broadcastOn()
    {
        return new Channel('admin');
    }

    public function broadcastAs(){
        return 'ActualizarProducto';
    }

    public function broadcastWith()
    {
        return [
            'producto' => $this->producto
        ];
    }
}
