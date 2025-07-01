<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;

Route::get('all-post', [PostController::class, 'all']);


Route::middleware(['auth:api'])->group(function () {
    Route::put('posts-status/{id}', [PostController::class, 'status']);
    Route::apiResource('posts', PostController::class);
});
