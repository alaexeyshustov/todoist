<?php

namespace App\Actions;

class MarkdownTodoParser
{
    /**
     * Parse markdown into a structured list of todo items with optional subtasks.
     *
     * @return array<int, array{title: string, done: bool, subtasks: array<int, array{title: string, done: bool}>}>
     */
    public static function parse(string $markdown): array
    {
        $items = [];
        $lastIndex = null;

        foreach (explode("\n", $markdown) as $line) {
            if (preg_match('/^- \[([ x])\] (.+)$/', $line, $m)) {
                $items[] = ['title' => trim($m[2]), 'done' => $m[1] === 'x', 'subtasks' => []];
                $lastIndex = count($items) - 1;
            } elseif (preg_match('/^  - \[([ x])\] (.+)$/', $line, $m) && $lastIndex !== null) {
                $items[$lastIndex]['subtasks'][] = ['title' => trim($m[2]), 'done' => $m[1] === 'x'];
            }
        }

        return $items;
    }

    public static function extractHeading(string $markdown): ?string
    {
        foreach (explode("\n", $markdown) as $line) {
            if (preg_match('/^# (.+)$/', $line, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }
}
