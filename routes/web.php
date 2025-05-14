<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;

// Ensure Breeze authentication routes are loaded
require base_path('routes/auth.php');

Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::middleware('auth')->group(function () {
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/game/create', [GameController::class, 'create'])->name('game.create');
    Route::get('/game/{id}/join', [GameController::class, 'join'])->name('game.join');
    Route::get('/game/{id}', function ($id) {
        return view('game', ['gameId' => $id]);
    })->name('game.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__ . '/auth.php';
