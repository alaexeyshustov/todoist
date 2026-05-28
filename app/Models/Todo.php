<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\Recurrence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $todo_list_id
 * @property int|null $parent_id
 * @property string $title
 * @property bool $done
 * @property Carbon|null $due_at
 * @property Priority|null $priority
 * @property Recurrence|null $recurrence
 */
class Todo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'todo_list_id',
        'parent_id',
        'title',
        'done',
        'due_at',
        'priority',
        'recurrence',
    ];

    protected function casts(): array
    {
        return [
            'done' => 'boolean',
            'due_at' => 'date',
            'priority' => Priority::class,
            'recurrence' => Recurrence::class,
        ];
    }

    /** @return BelongsTo<TodoList, $this> */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    /** @return BelongsTo<Todo, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Todo::class, 'parent_id');
    }

    /** @return HasMany<Todo, $this> */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Todo::class, 'parent_id');
    }

    /** @param Builder<Todo> $query */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_at', '<=', Carbon::today());
    }

    /** @param Builder<Todo> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 WHEN 'low' THEN 2 ELSE 3 END");
    }
}
