<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// PusakaV3 App endpoints (public, no auth needed)
Route::post('/pusaka/verify', [AuthController::class, 'verify']);
Route::get('/config', [AuthController::class, 'config']);
