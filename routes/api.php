<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PublicStatusController;
use App\Http\Controllers\Public\AmbilTiketController;
use App\Http\Controllers\Operator\OperatorQueueController;
use App\Http\Controllers\Sse\QueueSseController;

Route::prefix('public')->group(function () {
    Route::get('/status', [PublicStatusController::class, 'index']);
    Route::post('/tickets', [AmbilTiketController::class, 'take']);
});

Route::get('/sse/antrian', [QueueSseController::class, 'stream']);

Route::middleware('auth:sanctum')->prefix('operator')->group(function () {
    Route::get('/lokets', [OperatorQueueController::class, 'myLokets']);
    Route::post('/call-next', [OperatorQueueController::class, 'callNext']);
    Route::post('/recall', [OperatorQueueController::class, 'recall']);
    Route::post('/skip', [OperatorQueueController::class, 'skip']);
    Route::post('/serve', [OperatorQueueController::class, 'serve']);
});
