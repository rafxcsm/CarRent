<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;

Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::get('/admin/dashboard', [DashboardController::class, 'index']);