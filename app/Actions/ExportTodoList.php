<?php

namespace App\Actions;

use App\Models\Todo;
use App\Models\TodoList;

class ExportTodoList
{
    public function execute(TodoList $list): string
    {
        $lines = ["# {$list->name}"];

        $todos = $list->todos()
            ->whereNull('parent_id')
            ->with(['subtasks' => fn ($q) => $q->where('todo_list_id', $list->id)->orderBy('id')])
            ->orderBy('id')
            ->get();

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
