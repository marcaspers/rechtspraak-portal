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
});
