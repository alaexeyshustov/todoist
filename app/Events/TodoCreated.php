<?php

namespace App\Events;

use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TodoCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Todo $todo) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel(
            'todos.'.$this->todo->todoList->user_id
        );
    }

    public function broadcastAs(): string
    {
        return 'TodoCreated';
    }

    public function broadcastWith(): array
    {
        return ['todo' => (new TodoResource($this->todo->load('subtasks')))->resolve()];
    }
}
