<?php

use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a todo in a list', function () {
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Inbox']);

    $this->actingAs($user)
        ->postJson('/api/todos', ['list_id' => $list->id, 'title' => 'Buy milk'])
        ->assertCreated()
        ->assertJsonFragment(['title' => 'Buy milk', 'done' => false]);

    $this->assertDatabaseHas('todos', ['list_id' => $list->id, 'title' => 'Buy milk']);
});

it('returns todos filtered by list', function () {
    $user = User::factory()->create();
    $inbox = TodoList::create(['name' => 'Inbox']);
    $work = TodoList::create(['name' => 'Work']);
    $inboxTodo = Todo::create(['list_id' => $inbox->id, 'title' => 'Buy milk']);
    Todo::create(['list_id' => $work->id, 'title' => 'Send report']);

    $this->actingAs($user)
        ->getJson("/api/todos?list_id={$inbox->id}")
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['title' => 'Buy milk']);
});

it('marks a todo as done', function () {
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Inbox']);
    $todo = Todo::create(['list_id' => $list->id, 'title' => 'Buy milk']);

    $this->actingAs($user)
        ->patchJson("/api/todos/{$todo->id}", ['done' => true])
        ->assertOk()
        ->assertJsonFragment(['done' => true]);

    $this->assertDatabaseHas('todos', ['id' => $todo->id, 'done' => true]);
});

it('moves a todo to another list', function () {
    $user = User::factory()->create();
    $inbox = TodoList::create(['name' => 'Inbox']);
    $work = TodoList::create(['name' => 'Work']);
    $todo = Todo::create(['list_id' => $inbox->id, 'title' => 'Task']);

    $this->actingAs($user)
        ->patchJson("/api/todos/{$todo->id}", ['list_id' => $work->id])
        ->assertOk()
        ->assertJsonFragment(['list_id' => $work->id]);

    $this->assertDatabaseHas('todos', ['id' => $todo->id, 'list_id' => $work->id]);
});

it('deletes a todo', function () {
    $user = User::factory()->create();
    $list = TodoList::create(['name' => 'Inbox']);
    $todo = Todo::create(['list_id' => $list->id, 'title' => 'Buy milk']);

    $this->actingAs($user)
        ->deleteJson("/api/todos/{$todo->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
});
