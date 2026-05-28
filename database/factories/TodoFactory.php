<?php

namespace Database\Factories;

use App\Models\TodoList;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'todo_list_id' => TodoList::factory(),
            'parent_id' => null,
            'title' => $this->faker->sentence(4),
            'done' => false,
            'due_at' => null,
            'priority' => null,
            'recurrence' => null,
        ];
    }
}
