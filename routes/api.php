<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PublicStatusController;
use App\Http\Controllers\Public\AmbilTiketController;
use App\Http\Controllers\Operator\OperatorQueueController;
use App\Http\Controllers\Sse\QueueSseController;

Route::prefix('public')->group(function () {
    Route::get('/status', [PublicStatusController::class, 'index']);
    Route::get('/display/state', [PublicStatusController::class, 'displayState']);
    Route::post('/tickets', [AmbilTiketController::class, 'take']);
    Route::post('/tickets/{service_code}', [AmbilTiketController::class, 'take']);
});

Route::get('/sse/antrian', [QueueSseController::class, 'stream']);
Route::get('/sse/queue', [QueueSseController::class, 'stream']);


Route::middleware(['web', 'auth', 'role:ADMIN|SUPER_ADMIN'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->prefix('operator')
    ->group(function () {
        Route::get('/lokets', [OperatorQueueController::class, 'myLokets']);
        Route::get('/lokets/{loketCode}/current', [OperatorQueueController::class, 'current']);
        Route::post('/lokets/{loketCode}/call-next', [OperatorQueueController::class, 'callNextForLoket']);
        Route::post('/lokets/{loketCode}/recall', [OperatorQueueController::class, 'recallForLoket']);
        Route::post('/lokets/{loketCode}/skip', [OperatorQueueController::class, 'skipForLoket']);
        Route::post('/lokets/{loketCode}/serve', [OperatorQueueController::class, 'serveForLoket']);
        Route::post('/call-next', [OperatorQueueController::class, 'callNext']);
        Route::post('/recall', [OperatorQueueController::class, 'recall']);
        Route::post('/skip', [OperatorQueueController::class, 'skip']);
        Route::post('/serve', [OperatorQueueController::class, 'serve']);
    });
