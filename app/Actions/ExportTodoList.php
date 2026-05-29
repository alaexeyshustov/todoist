<?php

namespace App\Actions;

use App\Models\Todo;
use App\Models\TodoList;

class ExportTodoList
{
    public function execute(TodoList $list): string
    {
        $lines = ["# {$list->name}"];

        $todos = $list->todos()->whereNull('parent_id')->with('subtasks')->get();

        if ($todos->isNotEmpty()) {
            $lines[] = '';
            foreach ($todos as $todo) {
                $lines[] = $this->formatLine($todo);
                foreach ($todo->subtasks as $subtask) {
                    $lines[] = '  ' . $this->formatLine($subtask);
                }
            }
        }

        return implode("\n", $lines) . "\n";
    }

    private function formatLine(Todo $todo): string
    {
        $checkbox = $todo->done ? '[x]' : '[ ]';
        return "- {$checkbox} {$todo->title}";
    }
}
