<?php

use App\Actions\ImportTodoList;
use App\Models\TodoList;
use App\Models\User;

describe('ImportTodoList', function () {
    it('creates a list from the markdown heading', function () {
        $user = User::factory()->create();

        $list = (new ImportTodoList)->execute("# Shopping\n", $user);

        expect($list->name)->toBe('Shopping');
        expect(TodoList::where('name', 'Shopping')->exists())->toBeTrue();
    });

    it('creates an incomplete todo', function () {
        $user = User::factory()->create();

        $list = (new ImportTodoList)->execute("# Work\n\n- [ ] Write report\n", $user);

        $todo = $list->todos()->first();
        expect($todo->title)->toBe('Write report');
        expect($todo->done)->toBeFalse();
    });

    it('creates a done todo', function () {
        $user = User::factory()->create();

        $list = (new ImportTodoList)->execute("# Work\n\n- [x] Write report\n", $user);

        expect($list->todos()->first()->done)->toBeTrue();
    });

    it('creates a subtask linked to its parent', function () {
        $user = User::factory()->create();

        $list = (new ImportTodoList)->execute("# Shopping\n\n- [ ] Groceries\n  - [ ] Milk\n", $user);

        $parent = $list->todos()->whereNull('parent_id')->first();
        $child = $list->todos()->whereNotNull('parent_id')->first();

        expect($parent->title)->toBe('Groceries');
        expect($child->title)->toBe('Milk');
        expect($child->parent_id)->toBe($parent->id);
    });

    it('uses a name override instead of the heading', function () {
        $user = User::factory()->create();

        $list = (new ImportTodoList)->execute("# Shopping\n\n- [ ] Buy milk\n", $user, 'My Groceries');

        expect($list->name)->toBe('My Groceries');
    });

    it('uses the name override when no heading is present', function () {
        $user = User::factory()->create();

        $list = (new ImportTodoList)->execute("- [ ] Buy milk\n", $user, 'Fallback Name');

        expect($list->name)->toBe('Fallback Name');
    });

    it('truncates a heading longer than 255 characters to avoid a DB error', function () {
        $user = User::factory()->create();
        $longHeading = str_repeat('A', 300);

        $list = (new ImportTodoList)->execute("# {$longHeading}\n", $user);

        expect(strlen($list->name))->toBeLessThanOrEqual(255);
    });

    it('round-trips an exported list', function () {
        $user = User::factory()->create();
        $original = \App\Models\TodoList::factory()->create(['name' => 'Shopping', 'user_id' => $user->id]);
        $parent = \App\Models\Todo::factory()->create(['todo_list_id' => $original->id, 'title' => 'Groceries', 'done' => false]);
        \App\Models\Todo::factory()->create(['todo_list_id' => $original->id, 'parent_id' => $parent->id, 'title' => 'Milk', 'done' => true]);

        $markdown = (new \App\Actions\ExportTodoList)->execute($original);
        $imported = (new ImportTodoList)->execute($markdown, $user);

        expect($imported->name)->toBe('Shopping');

        $importedParent = $imported->todos()->whereNull('parent_id')->first();
        expect($importedParent->title)->toBe('Groceries');
        expect($importedParent->done)->toBeFalse();

        $importedChild = $imported->todos()->whereNotNull('parent_id')->first();
        expect($importedChild->title)->toBe('Milk');
        expect($importedChild->done)->toBeTrue();
        expect($importedChild->parent_id)->toBe($importedParent->id);
    });
});
