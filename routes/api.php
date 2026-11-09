<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserVehicleController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\ClientDirectoryController;
use App\Http\Controllers\SuperAdminClientController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/signup', [SignupController::class, 'store']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/clients', [ClientDirectoryController::class, 'index']);

/*
|--------------------------------------------------------------------------
| USER ROUTES (EXPO)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'user', 'tenant.subscription'])->group(function () {

    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::get('/user/notifications', [NotificationController::class, 'index']);
    Route::post('/user/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/user/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::get('/user/vehicles', [UserVehicleController::class, 'index']);
    Route::get('/user/vehicles/{id}/availability', [UserVehicleController::class, 'availability']);
 Route::get('/user/rentals', [RentalController::class, 'userRentals']);
    Route::post('/user/rentals', [RentalController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (VUE ADMIN)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'admin', 'tenant.subscription'])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/rentals', [RentalController::class, 'adminRentals']);
    Route::post('/rentals/{id}/resolve-maintenance', [RentalController::class, 'resolveMaintenance']);
    Route::post('/rentals/verify-qr', [RentalController::class, 'verifyQr']);
    Route::put('/rentals/{id}/discount', [RentalController::class, 'verifyDiscount']);
    Route::put('/rentals/{id}/approve', [RentalController::class, 'approve']);
    Route::put('/rentals/{id}/deny', [RentalController::class, 'deny']);

    Route::middleware('client.admin')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::get('/vehicles/{id}/availability', [VehicleController::class, 'availability']);
        Route::post('/vehicles/{id}/maintenance', [VehicleController::class, 'saveMaintenance']);
        Route::delete('/vehicles/{id}/maintenance', [VehicleController::class, 'deleteMaintenance']);
        Route::get('/available-vehicles', [VehicleController::class, 'availableForDate']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);
        Route::get('/owners', [OwnerController::class, 'index']);
        Route::delete('/owners/{id}', [OwnerController::class, 'destroy']);
        Route::get('/business', [BusinessProfileController::class, 'show']);
        Route::put('/business', [BusinessProfileController::class, 'update']);
        Route::get('/staff', [AdminStaffController::class, 'index']);
        Route::post('/staff', [AdminStaffController::class, 'store']);
        Route::put('/staff/{id}', [AdminStaffController::class, 'update']);
    });
});

/*
|--------------------------------------------------------------------------
| PLATFORM SUPER ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')->middleware(['auth:sanctum', 'super.admin'])->group(function () {
    Route::post('/logout', [SuperAdminAuthController::class, 'logout']);
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index']);
    Route::get('/clients', [SuperAdminClientController::class, 'index']);
    Route::get('/clients/{client}/report', [SuperAdminClientController::class, 'report']);
    Route::post('/clients', [SuperAdminClientController::class, 'store']);
    Route::put('/clients/{client}', [SuperAdminClientController::class, 'update']);
    Route::put('/clients/{client}/subscription', [SuperAdminClientController::class, 'updateSubscription']);
});
