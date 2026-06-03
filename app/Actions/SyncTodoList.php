<?php

namespace App\Actions;

use App\Models\TodoList;

class SyncTodoList
{
    public function execute(TodoList $list, string $markdown): TodoList
    {
        $list->todos()->forceDelete();

        $lines = explode("\n", $markdown);
        $lastParent = null;

        foreach ($lines as $line) {
            if (preg_match('/^- \[([ x])\] (.+)$/', $line, $m)) {
                $lastParent = $list->todos()->create([
                    'title' => trim($m[2]),
                    'done' => $m[1] === 'x',
                ]);
            } elseif (preg_match('/^  - \[([ x])\] (.+)$/', $line, $m) && $lastParent) {
                $list->todos()->create([
                    'title' => trim($m[2]),
                    'done' => $m[1] === 'x',
                    'parent_id' => $lastParent->id,
                ]);
            }
        }

        return $list->fresh();
    }
}
