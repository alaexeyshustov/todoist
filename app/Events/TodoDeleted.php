<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TodoDeleted implements ShouldBroadcast
{
    public function __construct(public readonly int $todoId) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('todos');
    }

    public function broadcastAs(): string
    {
        return 'TodoDeleted';
    }
}
