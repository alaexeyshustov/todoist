<?php

namespace App\Http\Controllers;

use App\Events\TodoCreated;
use App\Events\TodoDeleted;
use App\Events\TodoUpdated;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Jobs\CreateNextRecurrence;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TodoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $todos = Todo::query()
            ->whereHas('todoList', fn ($q) => $q->where('user_id', $request->user()->id))
            ->whereNull('parent_id')
            ->when($request->has('list'), fn ($q) => $q->where('todo_list_id', $request->integer('list')))
            ->ordered()
            ->get();

        return TodoResource::collection($todos);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $todo = Todo::create($request->validated());

        broadcast(new TodoCreated($todo->load('subtasks')))->toOthers();

        return (new TodoResource($todo))->response()->setStatusCode(201);
    }

    public function show(Request $request, Todo $todo): TodoResource
    {
        $this->authorize('view', $todo);

        return new TodoResource($todo->load('subtasks'));
    }

    public function update(UpdateTodoRequest $request, Todo $todo): TodoResource
    {
        $this->authorize('update', $todo);

        $todo->update($request->validated());

        if ($todo->wasChanged('done') && $todo->done && $todo->recurrence) {
            dispatch(new CreateNextRecurrence($todo));
        }

        broadcast(new TodoUpdated($todo->load('subtasks')))->toOthers();

        return new TodoResource($todo->load('subtasks'));
    }

    public function destroy(Request $request, Todo $todo): JsonResponse
    {
        $this->authorize('delete', $todo);
        $userId = $todo->todoList->user_id;
        $todoId = $todo->id;
        $todo->delete();

        broadcast(new TodoDeleted($todoId, $userId))->toOthers();

        return response()->json(null, 204);
    }

    public function trashed(Request $request): AnonymousResourceCollection
    {
        $todos = Todo::onlyTrashed()
            ->whereHas('todoList', fn ($q) => $q->where('user_id', $request->user()->id))
            ->get();

        return TodoResource::collection($todos);
    }

    public function restore(Request $request, int $id): TodoResource
    {
        $todo = Todo::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $todo);
        $todo->restore();

        broadcast(new TodoUpdated($todo->load('subtasks')))->toOthers();

        return new TodoResource($todo);
    }

    // Cascades: the parent_id FK with cascadeOnDelete will also hard-delete any subtasks.
    public function forceDelete(Request $request, int $id): JsonResponse
    {
        $todo = Todo::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $todo);
        $userId = $todo->todoList->user_id;
        $todoId = $todo->id;
        $todo->forceDelete();

        broadcast(new TodoDeleted($todoId, $userId))->toOthers();

        return response()->json(null, 204);
    }
}
