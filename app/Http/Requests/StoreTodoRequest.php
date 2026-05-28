<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\Recurrence;
use App\Models\Todo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTodoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'todo_list_id' => ['required', 'integer', Rule::exists('todo_lists', 'id')],
            'parent_id' => ['nullable', 'integer', Rule::exists('todos', 'id'), $this->noSubtaskNesting()],
            'title' => ['required', 'string', 'max:500'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['nullable', new Enum(Priority::class)],
            'recurrence' => ['nullable', new Enum(Recurrence::class)],
        ];
    }

    private function noSubtaskNesting(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if ($value && Todo::where('id', $value)->whereNotNull('parent_id')->exists()) {
                $fail('A subtask cannot itself have subtasks.');
            }
        };
    }
}
