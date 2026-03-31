<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserVehicleController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\OwnerController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// User authentication
Route::post('/signup', [SignupController::class, 'store']);
Route::post('/login', [LoginController::class, 'login']);

// User vehicle & rental
Route::get('/user/vehicles', [UserVehicleController::class, 'index']);
Route::post('/user/rentals', [RentalController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    // Admin authentication
    Route::post('/login', [AdminAuthController::class, 'login']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Vehicle management
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::post('/vehicles', [VehicleController::class, 'store']);
    Route::put('/vehicles/{id}', [VehicleController::class, 'update']); // Corrected from POST
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);

    // Rental management
    Route::get('/rentals', [RentalController::class, 'index']);
    Route::put('/rentals/{id}/approve', [RentalController::class, 'approve']);
    Route::put('/rentals/{id}/deny', [RentalController::class, 'deny']);

    Route::get('/owners', [OwnerController::class, 'index']);
    Route::delete('/owners/{id}', [OwnerController::class, 'destroy']);

});