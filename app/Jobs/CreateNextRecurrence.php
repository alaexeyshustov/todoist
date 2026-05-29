<?php

namespace App\Jobs;

use App\Enums\Recurrence;
use App\Events\TodoCreated;
use App\Models\Todo;
use Illuminate\Support\Carbon;

class CreateNextRecurrence
{
    public function __construct(public readonly Todo $todo) {}

    public function handle(): void
    {
        $next = $this->todo->replicate(['done', 'parent_id', 'deleted_at']);
        $next->done = false;
        $next->due_at = $this->nextDueAt();
        $next->save();

        broadcast(new TodoCreated($next));
    }

    private function nextDueAt(): ?Carbon
    {
        $dueAt = $this->todo->due_at;

        if ($dueAt === null) {
            return null;
        }

        return match ($this->todo->recurrence) {
            Recurrence::Daily => $dueAt->copy()->addDay(),
            Recurrence::Weekly => $dueAt->copy()->addWeek(),
            Recurrence::Monthly => $dueAt->copy()->addMonth(),
            default => null,
        };
    }
}
