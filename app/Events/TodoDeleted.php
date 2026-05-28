<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TodoDeleted implements ShouldBroadcast
{
    public function __construct(public readonly int $todoId) {}

    public function broadcastOn(): Channel
    {
        return new Channel('todos');
    }

    public function broadcastAs(): string
    {
        return 'TodoDeleted';
    }
}
