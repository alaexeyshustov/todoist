<?php

use App\Models\TodoList;
use App\Models\User;
use App\Policies\TodoListPolicy;

describe('TodoListPolicy', function () {
    it('allows the owner to view their list', function () {
        $list = TodoList::factory()->create();
        expect((new TodoListPolicy)->view($list->user, $list))->toBeTrue();
    });

    it('denies a non-owner from viewing a list', function () {
        $list = TodoList::factory()->create();
        $other = User::factory()->create();
        expect((new TodoListPolicy)->view($other, $list))->toBeFalse();
    });

    it('allows the owner to update their list', function () {
        $list = TodoList::factory()->create();
        expect((new TodoListPolicy)->update($list->user, $list))->toBeTrue();
    });

    it('denies a non-owner from updating a list', function () {
        $list = TodoList::factory()->create();
        $other = User::factory()->create();
        expect((new TodoListPolicy)->update($other, $list))->toBeFalse();
    });

    it('allows the owner to delete their list', function () {
        $list = TodoList::factory()->create();
        expect((new TodoListPolicy)->delete($list->user, $list))->toBeTrue();
    });

    it('denies a non-owner from deleting a list', function () {
        $list = TodoList::factory()->create();
        $other = User::factory()->create();
        expect((new TodoListPolicy)->delete($other, $list))->toBeFalse();
    });
});
