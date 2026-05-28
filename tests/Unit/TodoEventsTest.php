<?php

use App\Events\TodoCreated;
use App\Events\TodoDeleted;
use App\Events\TodoUpdated;
use App\Models\Todo;
use Illuminate\Broadcasting\Channel;

dataset('todo', [
    fn () => new Todo(['id' => 1, 'title' => 'Buy milk', 'list_id' => 1]),
]);

it('TodoCreated broadcasts on the todos channel', function (Todo $todo) {
    expect((new TodoCreated($todo))->broadcastOn())->toBeInstanceOf(Channel::class)
        ->and((new TodoCreated($todo))->broadcastOn()->name)->toBe('todos');
})->with('todo');

it('TodoCreated broadcasts as TodoCreated', function (Todo $todo) {
    expect((new TodoCreated($todo))->broadcastAs())->toBe('TodoCreated');
})->with('todo');

it('TodoCreated exposes the todo', function (Todo $todo) {
    expect((new TodoCreated($todo))->todo)->toBe($todo);
})->with('todo');

it('TodoUpdated broadcasts on the todos channel', function (Todo $todo) {
    expect((new TodoUpdated($todo))->broadcastOn()->name)->toBe('todos');
})->with('todo');

it('TodoUpdated broadcasts as TodoUpdated', function (Todo $todo) {
    expect((new TodoUpdated($todo))->broadcastAs())->toBe('TodoUpdated');
})->with('todo');

it('TodoDeleted broadcasts on the todos channel', function () {
    expect((new TodoDeleted(42))->broadcastOn()->name)->toBe('todos');
});

it('TodoDeleted broadcasts as TodoDeleted', function () {
    expect((new TodoDeleted(42))->broadcastAs())->toBe('TodoDeleted');
});

it('TodoDeleted exposes the todoId', function () {
    expect((new TodoDeleted(42))->todoId)->toBe(42);
});
