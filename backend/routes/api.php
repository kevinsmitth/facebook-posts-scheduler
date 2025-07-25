<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SendLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('posts', PostController::class)->except('update');
    Route::post('posts/{post}/retry', [PostController::class, 'retry'])->name('posts.retry');

    Route::get('send-logs', [SendLogController::class, 'index'])->name('send-logs.index');
    Route::get('send-logs/{post}', [SendLogController::class, 'postLogs'])->name('send-logs.post');
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

require __DIR__ . '/auth.php';
