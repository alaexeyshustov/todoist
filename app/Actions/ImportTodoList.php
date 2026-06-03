<?php

namespace App\Actions;

use App\Models\TodoList;
use App\Models\User;

class ImportTodoList
{
    public function execute(string $markdown, User $user, ?string $nameOverride = null): TodoList
    {
        $name = substr($nameOverride ?? MarkdownTodoParser::extractHeading($markdown) ?? 'Imported', 0, 255);
        /** @var TodoList $list */
        $list = $user->todoLists()->create(['name' => $name]);

        foreach (MarkdownTodoParser::parse($markdown) as $item) {
            $parent = $list->todos()->create(['title' => $item['title'], 'done' => $item['done']]);
            foreach ($item['subtasks'] as $sub) {
                $list->todos()->create(['title' => $sub['title'], 'done' => $sub['done'], 'parent_id' => $parent->id]);
            }
        }

        return $list;
    }
}
