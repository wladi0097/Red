<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'destroy']);
});
