<?php

namespace App\Http\Controllers;

use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TodayController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $todos = Todo::query()
            ->whereHas('todoList', fn ($q) => $q->where('user_id', $request->user()->id))
            ->whereNull('parent_id')
            ->dueToday()
            ->where('done', false)
            ->ordered()
            ->get();

        return TodoResource::collection($todos);
    }
}
