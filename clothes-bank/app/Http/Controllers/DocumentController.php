<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\ServiceUser;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function registrationForms()
    {
        $completed = Registration::with('serviceUser')
            ->latest()
            ->get();

        $unregistered = ServiceUser::doesntHave('registration')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'surname']);

        return Inertia::render('Documents/RegistrationFormsIndex', [
            'completed' => $completed,
            'unregistered' => $unregistered,
        ]);
    }
}
