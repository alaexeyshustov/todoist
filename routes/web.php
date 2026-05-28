<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', [AppController::class, 'index'])->middleware('auth');
