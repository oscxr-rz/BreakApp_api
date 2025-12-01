<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActualizarCategoria implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $categoria;

    public function __construct($categoria)
    {
        $this->categoria = $categoria;
    }
    public function broadcastOn()
    {
        return new Channel('admin');
    }

    public function broadcastWith()
    {
        return [
            'categoria' => $this->categoria
        ];
    }
}
