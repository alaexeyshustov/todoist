<?php

use App\Models\Todo;
use App\Models\User;
use App\Policies\TodoPolicy;

describe('TodoPolicy', function () {
    it('allows the owner to view their todo', function () {
        $todo = Todo::factory()->create();
        expect((new TodoPolicy)->view($todo->todoList->user, $todo))->toBeTrue();
    });

    it('denies a non-owner from viewing a todo', function () {
        $todo = Todo::factory()->create();
        $other = User::factory()->create();
        expect((new TodoPolicy)->view($other, $todo))->toBeFalse();
    });

    it('allows the owner to update their todo', function () {
        $todo = Todo::factory()->create();
        expect((new TodoPolicy)->update($todo->todoList->user, $todo))->toBeTrue();
    });

    it('denies a non-owner from updating a todo', function () {
        $todo = Todo::factory()->create();
        $other = User::factory()->create();
        expect((new TodoPolicy)->update($other, $todo))->toBeFalse();
    });

    it('allows the owner to delete their todo', function () {
        $todo = Todo::factory()->create();
        expect((new TodoPolicy)->delete($todo->todoList->user, $todo))->toBeTrue();
    });

    it('denies a non-owner from deleting a todo', function () {
        $todo = Todo::factory()->create();
        $other = User::factory()->create();
        expect((new TodoPolicy)->delete($other, $todo))->toBeFalse();
    });

    it('allows the owner to restore their trashed todo', function () {
        $todo = Todo::factory()->create();
        expect((new TodoPolicy)->restore($todo->todoList->user, $todo))->toBeTrue();
    });

    it('denies a non-owner from restoring a trashed todo', function () {
        $todo = Todo::factory()->create();
        $other = User::factory()->create();
        expect((new TodoPolicy)->restore($other, $todo))->toBeFalse();
    });

    it('allows the owner to force delete their todo', function () {
        $todo = Todo::factory()->create();
        expect((new TodoPolicy)->forceDelete($todo->todoList->user, $todo))->toBeTrue();
    });

    it('denies a non-owner from force deleting a todo', function () {
        $todo = Todo::factory()->create();
        $other = User::factory()->create();
        expect((new TodoPolicy)->forceDelete($other, $todo))->toBeFalse();
    });
});
