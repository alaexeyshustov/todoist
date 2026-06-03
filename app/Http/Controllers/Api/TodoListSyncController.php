<?php

namespace App\Http\Controllers\Api;

use App\Actions\SyncTodoList;
use App\Http\Controllers\Controller;
use App\Http\Resources\TodoListResource;
use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoListSyncController extends Controller
{
    public function __invoke(Request $request, TodoList $list): JsonResponse
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $markdown = $request->getContent();
        $list = (new SyncTodoList)->execute($list, $markdown);

        return (new TodoListResource($list))->response();
    }
}
