<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(TodoList::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $list = TodoList::create($validated);

        return response()->json($list, 201);
    }

    public function update(Request $request, TodoList $list): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $list->update($validated);

        return response()->json($list);
    }

    public function destroy(TodoList $list): JsonResponse
    {
        $list->delete();

        return response()->json(null, 204);
    }
}
