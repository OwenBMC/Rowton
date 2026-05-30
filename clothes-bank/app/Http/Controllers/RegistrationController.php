<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\ServiceUser;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    public function index(?ServiceUser $service_user = null)
    {
        // Transform the selected user for the form
        $selectedUser = $service_user ? $service_user->load(['nextOfKin', 'doctors', 'registration']) : null;

        $unregisteredUsers = ServiceUser::doesntHave('registration')
            ->get(['id', 'first_name', 'middle_names', 'surname', 'nickname'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ]);

        return Inertia::render('RegistrationForm', [
            'serviceUserId' => $service_user?->id, // <-- pass the prop
            'service_user' => $selectedUser,
            'unregistered_users' => $unregisteredUsers,
        ]);
    }

    public function show(Registration $registration)
    {
        $registration->load([
            'serviceUser',
            'serviceUser.nextOfKin',
            'serviceUser.doctors.practice',
        ]);

        return response()->json([
            'registration' => $registration,
        ]);
    }

    public function showJson($service_user = null)
    {
        $serviceUser = $service_user ? ServiceUser::with(['nextOfKin', 'doctors'])->find($service_user) : null;

        return response()->json($serviceUser);
    }

    /**
     * Handle form submission via Axios
     */
    public function submitJson(Request $request, ?ServiceUser $service_user = null)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_names' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required',
            'address' => 'nullable|string',
            'postcode' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'food_allergies' => 'boolean',

            'referral_date' => 'required|date',

            // IDs
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        if (! $service_user) {
            $service_user = ServiceUser::create([
                'first_name' => $data['first_name'],
                'middle_names' => $data['middle_names'] ?? null,
                'surname' => $data['surname'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'address' => $data['address'],
                'postcode' => $data['postcode'],
                'contact_number' => $data['contact_number'] ?? null,
                'food_allergies' => $data['food_allergies'] ?? false,
            ]);
        } else {
            $service_user->update([
                'first_name' => $data['first_name'],
                'middle_names' => $data['middle_names'] ?? null,
                'surname' => $data['surname'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'address' => $data['address'],
                'postcode' => $data['postcode'],
                'contact_number' => $data['contact_number'] ?? null,
                'food_allergies' => $data['food_allergies'] ?? false,
            ]);
        }

        $nextOfKinId = null;

        if ($request->filled('nok_name')) {
            $nok = $service_user->nextOfKin()->updateOrCreate(
                ['service_user_id' => $service_user->id],
                [
                    'name' => $request->nok_name,
                    'relationship' => $request->nok_relationship,
                    'address' => $request->nok_address,
                    'contact_number' => $request->nok_contact_number,
                ]
            );

            $nextOfKinId = $nok->id;
        }

        $doctorId = $data['doctor_id'] ?? null;

        if ($doctorId) {
            // attach doctor to service user (many-to-many)
            $service_user->doctors()->syncWithoutDetaching([$doctorId]);
        }

        $registration = Registration::updateOrCreate(
            ['service_user_id' => $service_user->id],
            [
                'referral_date' => $data['referral_date'],

                'next_of_kin_id' => $nextOfKinId,
                'doctor_id' => $doctorId,
            ]
        );

        return response()->json([
            'message' => 'Registration saved successfully!',
            'registration_id' => $registration->id,
        ]);
    }

    public function view(Registration $registration)
    {
        $registration->load('serviceUser');

        return inertia('RegistrationView', [
            'registration' => $registration,
        ]);
    }
}
