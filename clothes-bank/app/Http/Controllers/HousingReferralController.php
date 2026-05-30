<?php

namespace App\Http\Controllers;

use App\Models\HousingReferral;
use Illuminate\Http\Request;

class HousingReferralController extends Controller
{
    public function index()
    {
        return HousingReferral::with('serviceUser')
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_user_id' => 'required|exists:service_users,id',

            'gender' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'contact_number' => 'nullable|string',

            'national_insurance_number' => 'nullable|string',
            'nationality' => 'nullable|string',
            'previous_address' => 'nullable|string',

            'prison' => 'boolean',
            'hospital' => 'boolean',
            'fda' => 'nullable|string',

            'housing_points' => 'nullable|integer',

            'medical_conditions' => 'nullable|string',

            'first_contact' => 'nullable|string',
            'second_contact' => 'nullable|string',
            'third_contact' => 'nullable|string',

            'notes' => 'nullable|string',

            'outcome' => 'nullable|string',

            'sleeping_bag' => 'boolean',
        ]);

        $referral = HousingReferral::create($validated);

        return response()->json($referral);
    }

    public function update(Request $request, $id)
    {
        $referral = HousingReferral::findOrFail($id);

        $referral->update($request->all());

        return response()->json($referral);
    }

    public function show($id)
    {
        return HousingReferral::with('serviceUser')->findOrFail($id);
    }
}
