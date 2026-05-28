<?php

use App\Events\TodoCreated;
use App\Events\TodoDeleted;
use App\Events\TodoUpdated;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('broadcasts TodoCreated when a todo is created', function () {
    Event::fake();
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Inbox']);

    $this->actingAs($user)
        ->postJson('/api/todos', ['list_id' => $list->id, 'title' => 'Buy milk'])
        ->assertCreated();

    Event::assertDispatched(TodoCreated::class, fn ($e) => $e->todo->title === 'Buy milk');
});

it('broadcasts TodoUpdated when a todo is updated', function () {
    Event::fake();
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Inbox']);
    $todo = Todo::create(['list_id' => $list->id, 'title' => 'Buy milk']);

    $this->actingAs($user)
        ->patchJson("/api/todos/{$todo->id}", ['done' => true])
        ->assertOk();

    Event::assertDispatched(TodoUpdated::class, fn ($e) => $e->todo->id === $todo->id);
});

it('broadcasts TodoDeleted when a todo is deleted', function () {
    Event::fake();
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Inbox']);
    $todo = Todo::create(['list_id' => $list->id, 'title' => 'Buy milk']);

    $this->actingAs($user)
        ->deleteJson("/api/todos/{$todo->id}")
        ->assertNoContent();

    Event::assertDispatched(TodoDeleted::class, fn ($e) => $e->todoId === $todo->id);
});
