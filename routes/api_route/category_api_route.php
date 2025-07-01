<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CategoryController;

Route::get('all-category', [CategoryController::class, 'all']);

Route::middleware(['auth:api'])->group(function () {
    Route::put('categorys-status/{id}', [CategoryController::class, 'status']);
    Route::apiResource('categorys', CategoryController::class);
});