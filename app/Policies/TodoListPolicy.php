<?php

namespace App\Policies;

use App\Models\TodoList;
use App\Models\User;

class TodoListPolicy
{
    public function view(User $user, TodoList $todoList): bool
    {
        return $user->id == $todoList->user_id;
    }

    public function update(User $user, TodoList $todoList): bool
    {
        return $user->id == $todoList->user_id;
    }

    public function delete(User $user, TodoList $todoList): bool
    {
        return $user->id == $todoList->user_id;
    }
}
