<?php

use App\Enums\Recurrence;
use App\Jobs\CreateNextRecurrence;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

describe('Todo', function () {
    describe('index', function () {
        it('lists todos for the authenticated user', function () {
            $list = TodoList::factory()->create();
            Todo::factory()->count(3)->create(['todo_list_id' => $list->id]);
            Todo::factory()->create(); // another user's todo

            $this->actingAs($list->user)
                ->getJson('/api/todos')
                ->assertOk()
                ->assertJsonCount(3, 'data');
        });

        it('filters todos by list', function () {
            $user = User::factory()->create();
            $listA = TodoList::factory()->create(['user_id' => $user->id]);
            $listB = TodoList::factory()->create(['user_id' => $user->id]);
            Todo::factory()->count(2)->create(['todo_list_id' => $listA->id]);
            Todo::factory()->create(['todo_list_id' => $listB->id]);

            $this->actingAs($user)
                ->getJson("/api/todos?list={$listA->id}")
                ->assertOk()
                ->assertJsonCount(2, 'data');
        });

        it('excludes subtasks from the index', function () {
            $list = TodoList::factory()->create();
            $parent = Todo::factory()->create(['todo_list_id' => $list->id]);
            Todo::factory()->create(['todo_list_id' => $list->id, 'parent_id' => $parent->id]);

            $this->actingAs($list->user)
                ->getJson('/api/todos')
                ->assertOk()
                ->assertJsonCount(1, 'data');
        });

        it('requires authentication', function () {
            $this->getJson('/api/todos')->assertUnauthorized();
        });
    });

    describe('store', function () {
        it('creates a todo', function () {
            $list = TodoList::factory()->create();

            $this->actingAs($list->user)
                ->postJson('/api/todos', ['todo_list_id' => $list->id, 'title' => 'Buy milk'])
                ->assertStatus(201)
                ->assertJson(['data' => ['title' => 'Buy milk']]);

            $this->assertDatabaseHas('todos', ['title' => 'Buy milk', 'todo_list_id' => $list->id]);
        });

        it('creates a todo with due date and priority', function () {
            $list = TodoList::factory()->create();

            $this->actingAs($list->user)
                ->postJson('/api/todos', [
                    'todo_list_id' => $list->id,
                    'title' => 'Report',
                    'due_at' => '2026-06-01',
                    'priority' => 'high',
                ])
                ->assertStatus(201)
                ->assertJson(['data' => ['priority' => 'high', 'due_at' => '2026-06-01']]);
        });

        it('creates a subtask', function () {
            $list = TodoList::factory()->create();
            $parent = Todo::factory()->create(['todo_list_id' => $list->id]);

            $this->actingAs($list->user)
                ->postJson('/api/todos', [
                    'todo_list_id' => $list->id,
                    'parent_id' => $parent->id,
                    'title' => 'Step one',
                ])
                ->assertStatus(201)
                ->assertJson(['data' => ['parent_id' => $parent->id]]);
        });

        it('prevents nesting subtasks two levels deep', function () {
            $list = TodoList::factory()->create();
            $parent = Todo::factory()->create(['todo_list_id' => $list->id]);
            $subtask = Todo::factory()->create(['todo_list_id' => $list->id, 'parent_id' => $parent->id]);

            $this->actingAs($list->user)
                ->postJson('/api/todos', [
                    'todo_list_id' => $list->id,
                    'parent_id' => $subtask->id,
                    'title' => 'Too deep',
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['parent_id']);
        });

        it('returns 422 when title is missing', function () {
            $list = TodoList::factory()->create();
            $this->actingAs($list->user)
                ->postJson('/api/todos', ['todo_list_id' => $list->id])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('returns 422 when creating a todo in another user\'s list', function () {
            $otherList = TodoList::factory()->create();
            $user = User::factory()->create();

            $this->actingAs($user)
                ->postJson('/api/todos', ['todo_list_id' => $otherList->id, 'title' => 'Hack'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['todo_list_id']);
        });

        it('returns 422 when using another user\'s todo as parent', function () {
            $otherList = TodoList::factory()->create();
            $otherTodo = Todo::factory()->create(['todo_list_id' => $otherList->id]);
            $myList = TodoList::factory()->create();

            $this->actingAs($myList->user)
                ->postJson('/api/todos', [
                    'todo_list_id' => $myList->id,
                    'parent_id' => $otherTodo->id,
                    'title' => 'Hack',
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['parent_id']);
        });
    });

    describe('show', function () {
        it('returns a todo with subtasks', function () {
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id]);
            Todo::factory()->create(['todo_list_id' => $list->id, 'parent_id' => $todo->id]);

            $this->actingAs($list->user)
                ->getJson("/api/todos/{$todo->id}")
                ->assertOk()
                ->assertJsonCount(1, 'data.subtasks');
        });

        it('returns 403 for another user\'s todo', function () {
            $todo = Todo::factory()->create();
            $other = User::factory()->create();

            $this->actingAs($other)
                ->getJson("/api/todos/{$todo->id}")
                ->assertForbidden();
        });
    });

    describe('update', function () {
        it('marks a todo as done', function () {
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id]);

            $this->actingAs($list->user)
                ->patchJson("/api/todos/{$todo->id}", ['done' => true])
                ->assertOk()
                ->assertJson(['data' => ['done' => true]]);
        });

        it('updates title and clears due date', function () {
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => '2026-06-01']);

            $this->actingAs($list->user)
                ->patchJson("/api/todos/{$todo->id}", ['title' => 'New title', 'due_at' => null])
                ->assertOk()
                ->assertJson(['data' => ['title' => 'New title', 'due_at' => null]]);
        });

        it('dispatches CreateNextRecurrence when recurring todo is completed', function () {
            Bus::fake();
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create([
                'todo_list_id' => $list->id,
                'recurrence' => Recurrence::Daily,
                'due_at' => '2026-06-01',
            ]);

            $this->actingAs($list->user)
                ->patchJson("/api/todos/{$todo->id}", ['done' => true])
                ->assertOk();

            Bus::assertDispatched(CreateNextRecurrence::class);
        });

        it('does not dispatch CreateNextRecurrence when non-recurring todo is completed', function () {
            Bus::fake();
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id]);

            $this->actingAs($list->user)
                ->patchJson("/api/todos/{$todo->id}", ['done' => true])
                ->assertOk();

            Bus::assertNotDispatched(CreateNextRecurrence::class);
        });

        it('returns 403 for another user\'s todo', function () {
            $todo = Todo::factory()->create();
            $other = User::factory()->create();

            $this->actingAs($other)
                ->patchJson("/api/todos/{$todo->id}", ['done' => true])
                ->assertForbidden();
        });
    });

    describe('destroy', function () {
        it('soft deletes a todo', function () {
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id]);

            $this->actingAs($list->user)
                ->deleteJson("/api/todos/{$todo->id}")
                ->assertNoContent();

            $this->assertSoftDeleted('todos', ['id' => $todo->id]);
        });

        it('returns 403 for another user\'s todo', function () {
            $todo = Todo::factory()->create();
            $other = User::factory()->create();

            $this->actingAs($other)
                ->deleteJson("/api/todos/{$todo->id}")
                ->assertForbidden();
        });
    });
});
