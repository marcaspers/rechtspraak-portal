<?php

use App\Models\Ruling;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(auth()->check() ? route('rulings.index') : route('login'));
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/rulings', fn () => view('rulings.index'))->name('rulings.index');
    Route::get('/rulings/{ruling}', fn (Ruling $ruling) => view('rulings.show', ['ruling' => $ruling]))->name('rulings.show');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');
        Route::get('/feeds', fn () => view('admin.feeds'))->name('feeds');
        Route::get('/themes', fn () => view('admin.themes'))->name('themes');
        Route::get('/llm-settings', fn () => view('admin.llm-settings'))->name('llm-settings');
    });
});
