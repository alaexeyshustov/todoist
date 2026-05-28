<?php

use App\Models\TodoList;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('has many todos', function () {
    $list = new TodoList;

    expect($list->todos())->toBeInstanceOf(HasMany::class);
});
