<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/attendance', [AttendanceController::class, 'store'])
    ->name('attendance.store');

Route::post('/attendance/update', [AttendanceController::class, 'update'])
    ->name('attendance.update');