<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    
    Route::get('/health', HealthCheckController::class);

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/search', [PostController::class, 'search']);
    Route::get('/posts/{post}', [PostController::class, 'show'])->whereNumber('post');

    Route::get('/tags', [TagController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/posts/mine', [PostController::class, 'mine']);
        Route::get('/posts/mine/search', [PostController::class, 'searchMine']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update'])->whereNumber('post');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->whereNumber('post');
    });
});
