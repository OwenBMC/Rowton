<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ServiceUserController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/attendance', [ServiceUserController::class, 'getAttendance'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.index');

    

Route::get('/service-users', [ServiceUserController::class, 'allUsersJson'])
    ->middleware(['auth', 'verified'])
    ->name('service-users.all');

require __DIR__.'/settings.php';
