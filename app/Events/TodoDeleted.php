<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TodoDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $todoId,
        public readonly int $userId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("todos.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'TodoDeleted';
    }

    public function broadcastWith(): array
    {
        return ['id' => $this->todoId];
    }
}
