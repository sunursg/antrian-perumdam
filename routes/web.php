<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\OperatorTokenController;
use App\Http\Controllers\Sse\QueueSseController;

Route::get('/sse/antrian', [QueueSseController::class, 'stream']);

// Helper: issue Sanctum token for Operator (after login)
Route::get('/operator/token', [OperatorTokenController::class, 'issue'])->middleware(['auth','role:ADMIN|SUPER_ADMIN']);

Route::view('/', 'app');
Route::view('/{any}', 'app')->where('any', '^(?!api|admin).*$');
