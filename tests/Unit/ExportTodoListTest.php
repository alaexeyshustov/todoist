<?php

use App\Actions\ExportTodoList;
use App\Models\Todo;
use App\Models\TodoList;

describe('ExportTodoList', function () {
    it('exports an empty list as a heading only', function () {
        $list = TodoList::factory()->create(['name' => 'Shopping']);

        $result = (new ExportTodoList)->execute($list);

        expect($result)->toBe("# Shopping\n");
    });

    it('includes an incomplete todo', function () {
        $list = TodoList::factory()->create(['name' => 'Shopping']);
        Todo::factory()->create(['todo_list_id' => $list->id, 'title' => 'Buy milk', 'done' => false]);

        $result = (new ExportTodoList)->execute($list);

        expect($result)->toBe("# Shopping\n\n- [ ] Buy milk\n");
    });

    it('marks a done todo with [x]', function () {
        $list = TodoList::factory()->create(['name' => 'Shopping']);
        Todo::factory()->create(['todo_list_id' => $list->id, 'title' => 'Buy milk', 'done' => true]);

        $result = (new ExportTodoList)->execute($list);

        expect($result)->toBe("# Shopping\n\n- [x] Buy milk\n");
    });

    it('indents subtasks under their parent', function () {
        $list = TodoList::factory()->create(['name' => 'Shopping']);
        $parent = Todo::factory()->create(['todo_list_id' => $list->id, 'title' => 'Groceries', 'done' => false]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'parent_id' => $parent->id, 'title' => 'Milk', 'done' => false]);

        $result = (new ExportTodoList)->execute($list);

        expect($result)->toBe("# Shopping\n\n- [ ] Groceries\n  - [ ] Milk\n");
    });

    it('excludes soft-deleted todos', function () {
        $list = TodoList::factory()->create(['name' => 'Shopping']);
        $todo = Todo::factory()->create(['todo_list_id' => $list->id, 'title' => 'Buy milk']);
        $todo->delete();

        $result = (new ExportTodoList)->execute($list);

        expect($result)->toBe("# Shopping\n");
    });

    it('exports multiple todos', function () {
        $list = TodoList::factory()->create(['name' => 'Work']);
        Todo::factory()->create(['todo_list_id' => $list->id, 'title' => 'Write report', 'done' => false]);
        Todo::factory()->create(['todo_list_id' => $list->id, 'title' => 'Send email', 'done' => true]);

        $result = (new ExportTodoList)->execute($list);

        expect($result)->toContain("- [ ] Write report\n");
        expect($result)->toContain("- [x] Send email\n");
    });
});
