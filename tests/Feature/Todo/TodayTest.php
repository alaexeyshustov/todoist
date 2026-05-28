<?php

use App\Enums\Priority;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Support\Carbon;

describe('Today view', function () {
    it('returns todos due today', function () {
        $list = TodoList::factory()->create();
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::today()]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::tomorrow()]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => null]);

        $this->actingAs($list->user)
            ->getJson('/api/today')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('includes overdue todos', function () {
        $list = TodoList::factory()->create();
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::yesterday()]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::today()]);

        $this->actingAs($list->user)
            ->getJson('/api/today')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('excludes completed todos', function () {
        $list = TodoList::factory()->create();
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::today(), 'done' => true]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::today()]);

        $this->actingAs($list->user)
            ->getJson('/api/today')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('shows todos from all lists', function () {
        $user = User::factory()->create();
        $listA = TodoList::factory()->create(['user_id' => $user->id]);
        $listB = TodoList::factory()->create(['user_id' => $user->id]);
        Todo::factory()->create(['todo_list_id' => $listA->id, 'due_at' => Carbon::today()]);
        Todo::factory()->create(['todo_list_id' => $listB->id, 'due_at' => Carbon::today()]);

        $this->actingAs($user)
            ->getJson('/api/today')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('sorts by priority high first', function () {
        $list = TodoList::factory()->create();
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::today(), 'priority' => Priority::Low]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::today(), 'priority' => Priority::High]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'due_at' => Carbon::today(), 'priority' => null]);

        $response = $this->actingAs($list->user)
            ->getJson('/api/today')
            ->assertOk();

        $priorities = collect($response->json('data'))->pluck('priority')->all();
        expect($priorities)->toBe(['high', 'low', null]);
    });

    it('requires authentication', function () {
        $this->getJson('/api/today')->assertUnauthorized();
    });
});
