<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EligibilityRuleController;
use App\Http\Controllers\EnumDefinitionController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Reports\AttendanceReportController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceItemController;
use App\Http\Controllers\ServiceUserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TerminologyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/registration/{service_user?}', [RegistrationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('registration.index');


Route::middleware(['auth', 'verified'])->group(function () {

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

    Route::get('/', [ServiceUserController::class, 'getAttendance'])->name('home');
    // ->middleware(['auth', 'verified'])
    
    Route::get('/services-provided', function () {
        return Inertia::render('Services');
    })->name('services');
    
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
    
    Route::get('/reports/attendance', [AttendanceReportController::class, 'index']);
    
    Route::get('/attendance', [ServiceUserController::class, 'getAttendance'])
        // ->middleware(['auth', 'verified'])
        ->name('attendance.index');

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

Route::middleware(['auth', 'verified'])
    ->prefix('admin/settings')
    ->group(function () {

        Route::get('/', [SettingsController::class, 'index'])
            ->name('admin.settings');

        Route::get('/enums', [EnumDefinitionController::class, 'index'])
            ->name('admin.settings.enums');

        Route::get('/enums/{group}', [EnumDefinitionController::class, 'show']);

        Route::post('/enums/{group}', [EnumDefinitionController::class, 'store']);

        Route::put('/enums/{enumDefinition}', [EnumDefinitionController::class, 'update']);

        Route::delete('/enums/{enumDefinition}', [EnumDefinitionController::class, 'destroy']);

        Route::get('/terminology', [TerminologyController::class, 'index']);

        Route::put('/terminology/{terminology}', [
            TerminologyController::class,
            'update',
        ]);

        Route::get('/services', [
            ServiceItemController::class,
            'index',
        ]);

        Route::post('/services', [
            ServiceItemController::class,
            'store',
        ]);

        Route::put('/services/{serviceItem}', [
            ServiceItemController::class,
            'update',
        ]);

        Route::delete('/services/{serviceItem}', [
            ServiceItemController::class,
            'destroy',
        ]);

        Route::post('/services/categories', [
            ServiceCategoryController::class,
            'store',
        ]);

        Route::put('/services/categories/{serviceCategory}', [
            ServiceCategoryController::class,
            'update',
        ]);

        Route::delete('/services/categories/{serviceCategory}', [
            ServiceCategoryController::class,
            'destroy',
        ]);

        Route::post('/services/items', [
            ServiceItemController::class,
            'store',
        ]);

        Route::put('/services/items/{serviceItem}', [
            ServiceItemController::class,
            'update',
        ]);

        Route::delete('/services/items/{serviceItem}', [
            ServiceItemController::class,
            'destroy',
        ]);

        Route::get('rules', [EligibilityRuleController::class, 'index'])
            ->name('admin.settings.rules');

        Route::post('rules', [EligibilityRuleController::class, 'store']);

        Route::put('rules/{eligibilityRule}', [EligibilityRuleController::class, 'update']);

        Route::delete('rules/{eligibilityRule}', [EligibilityRuleController::class, 'destroy']);

        Route::put(
            '/services/categories/{serviceCategory}/frequency',
            [
                ServiceCategoryController::class,
                'updateFrequency',
            ]
        );

        Route::put(
            '/services/items/{serviceItem}/frequency',
            [
                ServiceItemController::class,
                'updateFrequency',
            ]
        );
    });

Route::get('/service-users', function () {
    return Inertia::render('ServiceUsers');
});

Route::get('/registration/view/{registration}', [RegistrationController::class, 'view']);

require __DIR__.'/settings.php';
