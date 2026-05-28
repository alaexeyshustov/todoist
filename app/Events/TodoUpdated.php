<?php

namespace App\Events;

use App\Models\Todo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TodoUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public readonly Todo $todo) {}

    public function broadcastOn(): Channel
    {
        return new Channel('todos');
    }

    public function broadcastAs(): string
    {
        return 'TodoUpdated';
    }
}
