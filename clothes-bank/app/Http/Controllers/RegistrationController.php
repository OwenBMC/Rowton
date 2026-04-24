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

    /**
     * Return the registration page data as JSON
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(?int $service_user = null)
    {
        $serviceUser = $service_user
            ? ServiceUser::with(['nextOfKin', 'doctor'])->find($service_user)
            : null;

        $unregisteredUsers = ServiceUser::doesntHave('registration')
            ->get(['id', 'first_name', 'middle_names', 'surname', 'nickname'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ]);

        return response()->json([
            'service_user' => $serviceUser,
            'unregistered_users' => $unregisteredUsers,
        ]);
    }

    public function showJson($service_user = null)
    {
        $serviceUser = $service_user ? ServiceUser::with(['nextOfKin', 'doctor'])->find($service_user) : null;

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
            'address' => 'nullable|string',
            'postcode' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'food_allergies' => 'boolean',
            'referral_date' => 'required|date',

            'nok_name' => 'nullable|string|max:255',
            'nok_relationship' => 'nullable|string|max:255',
            'nok_address' => 'nullable|string|max:255',
            'nok_contact_number' => 'nullable|string|max:255',

            'gp_name' => 'nullable|string|max:255',
            'gp_address' => 'nullable|string|max:255',
            'gp_contact_number' => 'nullable|string|max:255',

            'service_user_signature_date' => 'nullable|date',
            'volunteer_signature_date' => 'nullable|date',
        ]);

        // Create service user if not provided
        if (! $service_user) {
            $service_user = ServiceUser::create([
                'first_name' => $data['first_name'],
                'middle_names' => $data['middle_names'] ?? null,
                'surname' => $data['surname'],
                'nickname' => $data['nickname'] ?? null,
                'dob' => $data['dob'],
                'address' => $data['address'],
                'postcode' => $data['postcode'],
                'contact_number' => $data['contact_number'] ?? null,
                'food_allergies' => $data['food_allergies'] ?? false,
            ]);
        }

        // Create or update registration
        $registration = Registration::updateOrCreate(
            ['service_user_id' => $service_user->id],
            [
                'referral_date' => $data['referral_date'],
                'service_user_signature_date' => $data['service_user_signature_date'] ?? null,
                'volunteer_signature_date' => $data['volunteer_signature_date'] ?? null,
            ]
        );

        // Handle next of kin
        if ($request->filled('nok_name')) {
            $service_user->nextOfKin()->updateOrCreate([], [
                'name' => $data['nok_name'],
                'relationship' => $data['nok_relationship'],
                'address' => $data['nok_address'],
                'contact_number' => $data['nok_contact_number'],
            ]);
        }

        // Handle doctor
        if ($request->filled('gp_name')) {
            $service_user->doctors()->updateOrCreate([], [
                'name' => $data['gp_name'],
                'address' => $data['gp_address'],
                'contact_number' => $data['gp_contact_number'],
            ]);
        }

        return response()->json([
            'message' => 'Registration saved successfully!',
            'service_user_id' => $service_user->id,
        ]);
    }
}
