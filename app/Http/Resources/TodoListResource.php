<?php

namespace App\Http\Resources;

use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property TodoList $resource */
class TodoListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'todos_count' => $this->resource->todos_count ?? $this->resource->todos()->count(),
            'created_at' => $this->resource->created_at->toIso8601String(),
        ];
    }
}
