<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Reports\AttendanceReportController;
use App\Http\Controllers\ServiceUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ServiceUserController::class, 'getAttendance'])->name('home');
// ->middleware(['auth', 'verified'])

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/reports/attendance', [AttendanceReportController::class, 'index']);

Route::get('/attendance', [ServiceUserController::class, 'getAttendance'])
    // ->middleware(['auth', 'verified'])
    ->name('attendance.index');

Route::get('/registration/{service_user?}', [RegistrationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('registration.index');

Route::get('/housing-referrals', function () {
    return Inertia::render('HousingReferralFormsIndex');
})->name('housing-referrals.index');

Route::get('/housing-referrals/create', function () {
    return Inertia::render('HousingReferralForm');
})->name('housing-referrals.create');

Route::get('/housing-referrals/{id}/edit', function ($id) {
    return Inertia::render('HousingReferralForm', [
        'id' => $id,
    ]);
})->whereNumber('id')->name('housing-referrals.edit');
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/documents', function () {
        return Inertia::render('Documents');
    });

    Route::get('/documents/registration-forms',
        [DocumentController::class, 'registrationForms']
    );
    Route::get('/documents/housing-referral-forms',
        [DocumentController::class, 'HousingReferralForms']
    );

    Route::get('/blacklist', function () {
        return Inertia::render('Blacklist');
    });

});

Route::get('/service-users', function () {
    return Inertia::render('ServiceUsers');
});

Route::get('/registration/view/{registration}', [RegistrationController::class, 'view']);

require __DIR__.'/settings.php';
