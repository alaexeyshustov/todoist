<?php

use App\Models\Todo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('defaults done to false', function () {
    $todo = new Todo;

    expect($todo->done)->toBeFalse();
});

it('casts done to boolean', function () {
    $todo = new Todo(['done' => 1]);

    expect($todo->done)->toBeTrue()->toBeBool();
});

it('belongs to a TodoList', function () {
    $todo = new Todo;

    expect($todo->list())->toBeInstanceOf(BelongsTo::class);
});
