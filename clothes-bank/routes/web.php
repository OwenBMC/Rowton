<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ServiceUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

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

Route::get('/registration/{service_user?}', [RegistrationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('registration.index');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/documents', function () {
        return Inertia::render('Documents');
    });

    Route::get('/documents/registration-forms',
        [DocumentController::class, 'registrationForms']
    );

});

require __DIR__.'/settings.php';
