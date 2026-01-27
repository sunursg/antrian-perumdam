<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\AmbilTiketController;
use App\Http\Controllers\Public\DisplayController;
use App\Http\Controllers\Operator\OperatorPageController;
use App\Http\Controllers\Operator\OperatorTokenController;

Route::get('/', LandingController::class);

Route::get('/ambil-tiket', [AmbilTiketController::class, 'page']);
Route::get('/display', [DisplayController::class, 'page']);

Route::get('/operator', [OperatorPageController::class, 'page']);
Route::post('/operator/login', [OperatorPageController::class, 'login'])->middleware('web');
Route::post('/operator/logout', [OperatorPageController::class, 'logout'])->middleware('auth');

// Helper: issue Sanctum token for Operator (after login)
Route::get('/operator/token', [OperatorTokenController::class, 'issue'])->middleware('auth');
