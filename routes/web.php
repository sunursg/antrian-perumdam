<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\Counter\CounterAuthController;
use App\Http\Controllers\Operator\OperatorQueueController;
use App\Http\Controllers\Operator\OperatorTokenController;
use App\Http\Middleware\EnsureCounterOperator;

Route::redirect('/sse/antrian', '/api/sse/antrian', 302);

// Helper: issue Sanctum token for Operator (after login)
Route::get('/operator/token', [OperatorTokenController::class, 'issue'])->middleware(['auth:operator','role:ADMIN|SUPER_ADMIN']);

Route::prefix('counter')->group(function () {
    Route::get('/login', [CounterAuthController::class, 'showLogin'])->name('counter.login');
    Route::post('/login', [CounterAuthController::class, 'login'])->name('counter.login.submit');
    Route::post('/logout', [CounterAuthController::class, 'logout'])->name('counter.logout');
    Route::get('/', [CounterAuthController::class, 'index'])
        ->middleware(EnsureCounterOperator::class)
        ->name('counter.index');
});

Route::middleware(['auth:operator', 'role:ADMIN|SUPER_ADMIN'])
    ->prefix('counter-api')
    ->group(function () {
        Route::get('/lokets', [OperatorQueueController::class, 'myLokets']);
        Route::get('/lokets/{loketCode}/current', [OperatorQueueController::class, 'current']);
        Route::post('/lokets/{loketCode}/call-next', [OperatorQueueController::class, 'callNextForLoket']);
        Route::post('/lokets/{loketCode}/recall', [OperatorQueueController::class, 'recallForLoket']);
        Route::post('/lokets/{loketCode}/skip', [OperatorQueueController::class, 'skipForLoket']);
        Route::post('/lokets/{loketCode}/serve', [OperatorQueueController::class, 'serveForLoket']);
    });

Route::view('/', 'app');
Route::view('/{any}', 'app')->where('any', '^(?!api|admin).*$');

