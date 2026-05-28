<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['list_id', 'title', 'done'])]
class Todo extends Model
{
    protected $attributes = ['done' => false];

    protected function casts(): array
    {
        return ['done' => 'boolean'];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(TodoList::class, 'list_id');
    }
}
