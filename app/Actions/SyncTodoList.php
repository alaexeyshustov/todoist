<?php

namespace App\Actions;

use App\Models\TodoList;
use Illuminate\Support\Facades\DB;

class SyncTodoList
{
    public function execute(TodoList $list, string $markdown): TodoList
    {
        DB::transaction(function () use ($list, $markdown) {
            $list->todos()->forceDelete();

            foreach (MarkdownTodoParser::parse($markdown) as $item) {
                $parent = $list->todos()->create(['title' => $item['title'], 'done' => $item['done']]);
                foreach ($item['subtasks'] as $sub) {
                    $list->todos()->create(['title' => $sub['title'], 'done' => $sub['done'], 'parent_id' => $parent->id]);
                }
            }
        });

        return $list->loadCount('todos');
    }
}
