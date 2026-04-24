<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ServiceUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum']);

Route::post('/attendance', [AttendanceController::class, 'store'])
    ->name('attendance.store');

Route::post('/attendance/update', [AttendanceController::class, 'update'])
    ->name('attendance.update');

Route::get('/service-users', [ServiceUserController::class, 'allUsersJson'])->name('service-users.all');
Route::get('/service-users/unregistered', [ServiceUserController::class, 'getUnregistered'])->name('service-users.unregistered');

Route::post('/service-users', [ServiceUserController::class, 'store']);

Route::get('/service-users/{service_user?}', [RegistrationController::class, 'showJson']);
Route::post('/registration/{service_user?}', [RegistrationController::class, 'submitJson']);
