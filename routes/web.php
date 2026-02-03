<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\AmbilTiketController;
use App\Http\Controllers\Public\DisplayController;
use App\Http\Controllers\Operator\OperatorPageController;
use App\Http\Controllers\Operator\OperatorQueueController;
use App\Http\Controllers\Operator\OperatorTokenController;
use App\Http\Controllers\Sse\QueueSseController;

Route::redirect('/', '/display');
Route::view('/landing', 'pages.landing');

Route::get('/ambil-tiket', [AmbilTiketController::class, 'page']);
Route::post('/ambil-tiket', [AmbilTiketController::class, 'take']);
Route::get('/tiket/{ticket}', [AmbilTiketController::class, 'show']);
Route::get('/display', [DisplayController::class, 'page']);
Route::get('/sse/antrian', [QueueSseController::class, 'stream']);

Route::get('/operator', [OperatorPageController::class, 'page']);
Route::post('/operator/login', [OperatorPageController::class, 'login'])->middleware('web');
Route::post('/operator/logout', [OperatorPageController::class, 'logout'])->middleware('auth');

// Helper: issue Sanctum token for Operator (after login)
Route::get('/operator/token', [OperatorTokenController::class, 'issue'])->middleware(['auth','role:ADMIN|SUPER_ADMIN']);

Route::middleware(['auth','role:ADMIN|SUPER_ADMIN'])->group(function () {
    Route::post('/operator/call-next', [OperatorQueueController::class, 'callNext']);
    Route::post('/operator/recall/{ticket}', [OperatorQueueController::class, 'recallById']);
    Route::post('/operator/skip/{ticket}', [OperatorQueueController::class, 'skipById']);
    Route::post('/operator/serve/{ticket}', [OperatorQueueController::class, 'serveById']);
});
