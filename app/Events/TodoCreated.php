<?php

namespace App\Events;

use App\Models\Todo;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TodoCreated implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public readonly Todo $todo) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('todos');
    }

    public function broadcastAs(): string
    {
        return 'TodoCreated';
    }
}
