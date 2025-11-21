<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActualizarMenu implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $menu;

    public function __construct($menu)
    {
        $this->menu = $menu;
    }

    public function broadcastOn()
    {
        return new Channel('menu');
    }

    public function broadcastWith() {
        return [
            'menu' => $this->menu
        ];
    }
}
