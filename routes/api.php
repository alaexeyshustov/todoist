<?php

use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\TodoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.basic')->group(function () {
    Route::apiResource('lists', ListController::class);
    Route::apiResource('todos', TodoController::class);
});
