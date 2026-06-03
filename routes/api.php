<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\TodoListSyncController;
use App\Http\Controllers\TodayController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\TodoListController;
use App\Http\Controllers\TodoListExportController;
use App\Http\Controllers\TodoListImportController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth:sanctum,web')->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);

    Route::get('lists/{list}/export', TodoListExportController::class);
    Route::post('lists/import', TodoListImportController::class);
    Route::put('lists/{list}/sync', TodoListSyncController::class);
    Route::apiResource('lists', TodoListController::class);

    Route::get('todos/trashed', [TodoController::class, 'trashed']);
    Route::post('todos/{id}/restore', [TodoController::class, 'restore']);
    Route::delete('todos/{id}/force', [TodoController::class, 'forceDelete']);
    Route::apiResource('todos', TodoController::class);

    Route::get('today', TodayController::class);
});
