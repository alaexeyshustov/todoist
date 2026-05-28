<?php

namespace App\Http\Controllers\Api;

use App\Events\TodoCreated;
use App\Events\TodoDeleted;
use App\Events\TodoUpdated;
use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Todo::query();

        if ($request->has('list_id')) {
            $query->where('list_id', $request->integer('list_id'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'list_id' => 'required|integer|exists:lists,id',
            'title' => 'required|string|max:255',
        ]);

        $todo = Todo::create($validated);
        event(new TodoCreated($todo));

        return response()->json($todo, 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'done' => 'sometimes|boolean',
            'list_id' => 'sometimes|integer|exists:lists,id',
        ]);

        $todo->update($validated);
        event(new TodoUpdated($todo));

        return response()->json($todo);
    }

    public function destroy(Todo $todo): JsonResponse
    {
        $id = $todo->id;
        $todo->delete();
        event(new TodoDeleted($id));

        return response()->json(null, 204);
    }
}
