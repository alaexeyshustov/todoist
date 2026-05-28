<?php

use App\Events\TodoCreated;
use App\Events\TodoDeleted;
use App\Events\TodoUpdated;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

describe('Broadcasting', function () {
    describe('events', function () {
        it('broadcasts TodoCreated on the correct private channel when a todo is created', function () {
            Event::fake();
            $list = TodoList::factory()->create();

            $this->actingAs($list->user)
                ->postJson('/api/todos', ['todo_list_id' => $list->id, 'title' => 'Buy milk'])
                ->assertStatus(201);

            Event::assertDispatched(TodoCreated::class, function ($event) use ($list) {
                return $event->broadcastOn()->name === "private-todos.{$list->user_id}";
            });
        });

        it('broadcasts TodoUpdated when a todo is updated', function () {
            Event::fake();
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id]);

            $this->actingAs($list->user)
                ->patchJson("/api/todos/{$todo->id}", ['title' => 'Updated'])
                ->assertOk();

            Event::assertDispatched(TodoUpdated::class);
        });

        it('broadcasts TodoDeleted when a todo is soft deleted', function () {
            Event::fake();
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id]);

            $this->actingAs($list->user)
                ->deleteJson("/api/todos/{$todo->id}")
                ->assertNoContent();

            Event::assertDispatched(TodoDeleted::class, function ($event) use ($todo) {
                return $event->todoId === $todo->id;
            });
        });

        it('broadcasts TodoDeleted payload with the todo id', function () {
            $list = TodoList::factory()->create();
            $todo = Todo::factory()->create(['todo_list_id' => $list->id]);
            $event = new TodoDeleted($todo->id, $list->user_id);

            expect($event->broadcastWith())->toBe(['id' => $todo->id])
                ->and($event->broadcastAs())->toBe('TodoDeleted');
        });
    });

    describe('channel authorization', function () {
        it('authorizes user to join their own todos channel', function () {
            $user = User::factory()->create();

            $broadcaster = app(Broadcaster::class);
            $request = Request::create('/broadcasting/auth', 'POST', [
                'channel_name' => "private-todos.{$user->id}",
                'socket_id' => '123.456',
            ]);
            $request->setUserResolver(fn () => $user);

            $verify = new ReflectionMethod($broadcaster, 'verifyUserCanAccessChannel');

            // Should not throw — authorization succeeds
            expect(fn () => $verify->invoke($broadcaster, $request, "todos.{$user->id}"))
                ->not->toThrow(AccessDeniedHttpException::class);
        });

        it('denies user from joining another user\'s todos channel', function () {
            $user = User::factory()->create();
            $other = User::factory()->create();

            $broadcaster = app(Broadcaster::class);
            $request = Request::create('/broadcasting/auth', 'POST', [
                'channel_name' => "private-todos.{$other->id}",
                'socket_id' => '123.456',
            ]);
            $request->setUserResolver(fn () => $user);

            $verify = new ReflectionMethod($broadcaster, 'verifyUserCanAccessChannel');

            expect(fn () => $verify->invoke($broadcaster, $request, "todos.{$other->id}"))
                ->toThrow(AccessDeniedHttpException::class);
        });
    });
});
