<?php

namespace App\Jobs;

use App\Enums\Recurrence;
use App\Models\Todo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class CreateNextRecurrence implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Todo $todo) {}

    public function handle(): void
    {
        $next = $this->todo->replicate(['done', 'parent_id', 'deleted_at']);
        $next->done = false;
        $next->due_at = $this->nextDueAt();
        $next->save();
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
