<?php

namespace App\Http\Controllers;

use App\Actions\ImportTodoList;
use App\Http\Resources\TodoListResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoListImportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:md,txt', 'max:512'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $markdown = file_get_contents($request->file('file')->getRealPath());
        $list = (new ImportTodoList)->execute($markdown, $request->user(), $request->input('name'));

        return (new TodoListResource($list))->response()->setStatusCode(201);
    }
}
