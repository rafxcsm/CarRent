<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserVehicleController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\RentalController;

Route::post('/signup', [SignupController::class, 'store']);
Route::post('/login', [LoginController::class, 'login']);

Route::get('/user/vehicles', [UserVehicleController::class, 'index']);

Route::post('/user/rentals', [RentalController::class, 'store']);


Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::get('/admin/dashboard', [DashboardController::class, 'index']);

Route::get('/admin/vehicles', [VehicleController::class, 'index']);
Route::post('/admin/vehicles', [VehicleController::class, 'store']);
Route::post('/admin/vehicles/{id}', [VehicleController::class, 'update']);
Route::delete('/admin/vehicles/{id}', [VehicleController::class, 'destroy']);

Route::get('/admin/rentals', [RentalController::class, 'index']);
Route::put('/admin/rentals/{id}/approve', [RentalController::class, 'approve']);
Route::put('/admin/rentals/{id}/deny', [RentalController::class, 'deny']);