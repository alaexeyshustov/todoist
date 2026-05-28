<?php

use App\Http\Controllers\TodayController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\TodoListController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::apiResource('lists', TodoListController::class);

    Route::get('todos/trashed', [TodoController::class, 'trashed']);
    Route::post('todos/{id}/restore', [TodoController::class, 'restore']);
    Route::delete('todos/{id}/force', [TodoController::class, 'forceDelete']);
    Route::apiResource('todos', TodoController::class);

    Route::get('today', TodayController::class);
});
