<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ServiceUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum']);

Route::get('/attendance/today', [AttendanceController::class, 'today']);

Route::post('/attendance', [AttendanceController::class, 'store'])
    ->name('attendance.store');

Route::post('/attendance/update', [AttendanceController::class, 'update'])
    ->name('attendance.update');

Route::get('/service-users/{service_user}', [ServiceUserController::class, 'show']);
Route::get('/service-users', [ServiceUserController::class, 'allUsersJson'])->name('service-users.all');
Route::get('/service-users/unregistered', [ServiceUserController::class, 'getUnregistered'])->name('service-users.unregistered');
Route::get('/service-users', [ServiceUserController::class, 'index']);
Route::get('/service-users-full', [ServiceUserController::class, 'fullIndex']);
Route::put('/service-users/{serviceUser}', [ServiceUserController::class, 'update']);

Route::post('/service-users', [ServiceUserController::class, 'store']);

Route::get('/service-users/{service_user?}', [RegistrationController::class, 'showJson']);
Route::post('/registration/{service_user?}', [RegistrationController::class, 'submitJson']);
Route::get('/registration/{registration}', [RegistrationController::class, 'show'])
    ->name('registration.show');

Route::get('/practices', [PracticeController::class, 'index']);
Route::post('/practices', [PracticeController::class, 'store']);
Route::put('/practices/{practice}', [PracticeController::class, 'update']);

Route::get('/practices/{practice}/doctors', [DoctorController::class, 'byPractice']);
Route::post('/doctors', [DoctorController::class, 'store']);
Route::put('/doctors/{doctor}', [DoctorController::class, 'update']);
