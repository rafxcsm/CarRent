<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/dashboard', [AdminAuthController::class, 'dashboard']);