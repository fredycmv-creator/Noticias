<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [AuthController::class, 'create'])->name('login');
    Route::post('/ingresar', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/salir', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/noticias/{post}/eliminar', [PostController::class, 'confirm'])->name('posts.confirm');
    Route::resource('noticias', PostController::class)->parameters(['noticias' => 'post'])->names('posts')->except('show');
});
