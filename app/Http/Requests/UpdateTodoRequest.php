<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Enums\Recurrence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTodoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:500'],
            'done' => ['sometimes', 'boolean'],
            'due_at' => ['sometimes', 'nullable', 'date'],
            'priority' => ['sometimes', 'nullable', new Enum(Priority::class)],
            'recurrence' => ['sometimes', 'nullable', new Enum(Recurrence::class)],
        ];
    }
}
