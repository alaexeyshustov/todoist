<?php

use App\Enums\Priority;
use App\Enums\Recurrence;
use App\Jobs\CreateNextRecurrence;
use App\Models\Todo;
use App\Models\TodoList;

it('creates the next daily occurrence', function () {
    $list = TodoList::factory()->create();
    $todo = Todo::factory()->create([
        'todo_list_id' => $list->id,
        'recurrence' => Recurrence::Daily,
        'due_at' => '2026-06-01',
        'title' => 'Take vitamins',
        'done' => true,
    ]);

    (new CreateNextRecurrence($todo))->handle();

    $next = Todo::latest('id')->first();
    expect($next->title)->toBe('Take vitamins')
        ->and($next->done)->toBeFalse()
        ->and($next->due_at->toDateString())->toBe('2026-06-02');
});

it('creates the next weekly occurrence', function () {
    $list = TodoList::factory()->create();
    $todo = Todo::factory()->create([
        'todo_list_id' => $list->id,
        'recurrence' => Recurrence::Weekly,
        'due_at' => '2026-06-01',
    ]);

    (new CreateNextRecurrence($todo))->handle();

    $next = Todo::latest('id')->first();
    expect($next->due_at->toDateString())->toBe('2026-06-08');
});

it('creates the next monthly occurrence', function () {
    $list = TodoList::factory()->create();
    $todo = Todo::factory()->create([
        'todo_list_id' => $list->id,
        'recurrence' => Recurrence::Monthly,
        'due_at' => '2026-06-01',
    ]);

    (new CreateNextRecurrence($todo))->handle();

    $next = Todo::latest('id')->first();
    expect($next->due_at->toDateString())->toBe('2026-07-01');
});

it('creates next occurrence with null due_at when original has no due date', function () {
    $list = TodoList::factory()->create();
    $todo = Todo::factory()->create([
        'todo_list_id' => $list->id,
        'recurrence' => Recurrence::Daily,
        'due_at' => null,
    ]);

    (new CreateNextRecurrence($todo))->handle();

    $next = Todo::latest('id')->first();
    expect($next->due_at)->toBeNull();
});

it('preserves priority and recurrence on the next occurrence', function () {
    $list = TodoList::factory()->create();
    $todo = Todo::factory()->create([
        'todo_list_id' => $list->id,
        'recurrence' => Recurrence::Weekly,
        'priority' => Priority::High,
        'due_at' => '2026-06-01',
    ]);

    (new CreateNextRecurrence($todo))->handle();

    $next = Todo::latest('id')->first();
    expect($next->priority)->toBe(Priority::High)
        ->and($next->recurrence)->toBe(Recurrence::Weekly);
});

it('does not copy parent_id to next occurrence', function () {
    $list = TodoList::factory()->create();
    $parent = Todo::factory()->create(['todo_list_id' => $list->id]);
    $todo = Todo::factory()->create([
        'todo_list_id' => $list->id,
        'recurrence' => Recurrence::Daily,
        'due_at' => '2026-06-01',
        'parent_id' => $parent->id,
    ]);

    (new CreateNextRecurrence($todo))->handle();

    $next = Todo::latest('id')->first();
    expect($next->parent_id)->toBeNull();
});
