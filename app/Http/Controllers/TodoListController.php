<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoListRequest;
use App\Http\Requests\UpdateTodoListRequest;
use App\Http\Resources\TodoListResource;
use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TodoListController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return TodoListResource::collection(
            $request->user()->todoLists()->get()
        );
    }

    public function store(StoreTodoListRequest $request): JsonResponse
    {
        $list = $request->user()->todoLists()->create($request->validated());

        return (new TodoListResource($list))->response()->setStatusCode(201);
    }

    public function show(Request $request, TodoList $list): TodoListResource
    {
        $this->authorize('view', $list);

        return new TodoListResource($list);
    }

    public function update(UpdateTodoListRequest $request, TodoList $list): TodoListResource
    {
        $this->authorize('update', $list);
        $list->update($request->validated());

        return new TodoListResource($list);
    }

    public function destroy(Request $request, TodoList $list): JsonResponse
    {
        $this->authorize('delete', $list);
        $list->delete();

        return response()->json(null, 204);
    }
}
