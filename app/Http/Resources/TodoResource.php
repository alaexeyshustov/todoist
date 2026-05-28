<?php

namespace App\Http\Resources;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Todo $resource */
class TodoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'todo_list_id' => $this->resource->todo_list_id,
            'parent_id' => $this->resource->parent_id,
            'title' => $this->resource->title,
            'done' => $this->resource->done,
            'due_at' => $this->resource->due_at?->toDateString(),
            'priority' => $this->resource->priority?->value,
            'recurrence' => $this->resource->recurrence?->value,
            'subtasks' => $this->when(
                $this->resource->relationLoaded('subtasks'),
                fn () => TodoResource::collection($this->resource->subtasks)
            ),
            'created_at' => $this->resource->created_at->toIso8601String(),
        ];
    }
}
