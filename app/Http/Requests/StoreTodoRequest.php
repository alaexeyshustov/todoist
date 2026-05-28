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
            'todo_list_id' => [
                'required',
                'integer',
                Rule::exists('todo_lists', 'id')->where('user_id', $this->user()->id),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                $this->parentBelongsToUser(),
                $this->noSubtaskNesting(),
            ],
            'title' => ['required', 'string', 'max:500'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['nullable', new Enum(Priority::class)],
            'recurrence' => ['nullable', new Enum(Recurrence::class)],
        ];
    }

    private function parentBelongsToUser(): \Closure
    {
        return function ($attribute, $value, $fail) {
            if (! $value) {
                return;
            }

            $userId = $this->user()->id;
            $exists = Todo::where('id', $value)
                ->whereIn('todo_list_id', function ($query) use ($userId) {
                    $query->select('id')->from('todo_lists')->where('user_id', $userId);
                })
                ->exists();

            if (! $exists) {
                $fail('The selected parent todo does not belong to your lists.');
            }
        };
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
