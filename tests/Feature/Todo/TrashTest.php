<?php

use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;

describe('Trash', function () {
    it('lists trashed todos', function () {
        $list = TodoList::factory()->create();
        Todo::factory()->count(2)->create(['todo_list_id' => $list->id])->each->delete();
        Todo::factory()->create(['todo_list_id' => $list->id]); // not trashed

        $this->actingAs($list->user)
            ->getJson('/api/todos/trashed')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('restores a trashed todo', function () {
        $list = TodoList::factory()->create();
        $todo = Todo::factory()->create(['todo_list_id' => $list->id]);
        $todo->delete();

        $this->actingAs($list->user)
            ->postJson("/api/todos/{$todo->id}/restore")
            ->assertOk();

        $this->assertDatabaseHas('todos', ['id' => $todo->id, 'deleted_at' => null]);
    });

    it('permanently deletes a trashed todo', function () {
        $list = TodoList::factory()->create();
        $todo = Todo::factory()->create(['todo_list_id' => $list->id]);
        $todo->delete();

        $this->actingAs($list->user)
            ->deleteJson("/api/todos/{$todo->id}/force")
            ->assertNoContent();

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    });

    it('returns 403 when restoring another user\'s todo', function () {
        $todo = Todo::factory()->create();
        $todo->delete();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->postJson("/api/todos/{$todo->id}/restore")
            ->assertForbidden();
    });

    it('returns 403 when force deleting another user\'s todo', function () {
        $todo = Todo::factory()->create();
        $todo->delete();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->deleteJson("/api/todos/{$todo->id}/force")
            ->assertForbidden();
    });

    it('force deleting a parent hard-deletes its subtasks via cascade', function () {
        $list = TodoList::factory()->create();
        $parent = Todo::factory()->create(['todo_list_id' => $list->id]);
        $subtask = Todo::factory()->create(['todo_list_id' => $list->id, 'parent_id' => $parent->id]);
        $parent->delete();

        $this->actingAs($list->user)
            ->deleteJson("/api/todos/{$parent->id}/force")
            ->assertNoContent();

        $this->assertDatabaseMissing('todos', ['id' => $subtask->id]);
    });
});
