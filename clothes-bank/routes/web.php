<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ServiceUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ServiceUserController::class, 'getAttendance'])->name('home');
// ->middleware(['auth', 'verified'])

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/attendance', [ServiceUserController::class, 'getAttendance'])
    // ->middleware(['auth', 'verified'])
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

Route::get('/service-users', function () {
    return Inertia::render('ServiceUsers');
});

Route::get('/registration/view/{registration}', [RegistrationController::class, 'view']);

require __DIR__.'/settings.php';
