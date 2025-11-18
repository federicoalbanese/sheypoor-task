<?php

use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'store']);

Route::prefix('leaderboard')->group(function () {
    Route::middleware('throttle:300,1')->group(function () {
        Route::get('/', [LeaderboardController::class, 'index']);
        Route::get('/user/{userId}', [LeaderboardController::class, 'show']);
        Route::get('/user/{userId}/history', [LeaderboardController::class, 'history']);
    });

    Route::post('/', [LeaderboardController::class, 'award']);
});
