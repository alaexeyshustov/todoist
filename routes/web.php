<?php

use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', [AppController::class, 'index'])->middleware('auth');

Route::prefix('api')->middleware('auth.basic')->group(function () {
    Route::apiResource('lists', ListController::class)->except(['show']);
    Route::apiResource('todos', TodoController::class)->except(['show']);
});
