<?php

namespace App\Actions;

use App\Models\TodoList;
use App\Models\User;

class ImportTodoList
{
    public function execute(string $markdown, User $user, ?string $nameOverride = null): TodoList
    {
        $lines = explode("\n", $markdown);
        $name = substr($nameOverride ?? $this->extractHeading($lines) ?? 'Imported', 0, 255);
        /** @var TodoList $list */
        $list = $user->todoLists()->create(['name' => $name]);

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

        return $list;
    }

    private function extractHeading(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/^# (.+)$/', $line, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }
}
