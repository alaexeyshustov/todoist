<?php

namespace App\Http\Controllers;

use App\Actions\ExportTodoList;
use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TodoListExportController extends Controller
{
    public function __invoke(Request $request, TodoList $list): Response
    {
        $this->authorize('view', $list);

        $markdown = (new ExportTodoList)->execute($list);
        $filename = Str::slug($list->name) . '.md';

        return response($markdown)
            ->header('Content-Type', 'text/markdown')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
