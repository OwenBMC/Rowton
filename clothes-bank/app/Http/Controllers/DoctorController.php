<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    // GET /api/practices/{practice}/doctors
    public function byPractice($practiceId)
    {
        return Doctor::where('practice_id', $practiceId)
            ->orderBy('name')
            ->get();
    }

    // POST /api/doctors
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'practice_id' => 'required|exists:practices,id',
        ]);

        $doctor = Doctor::create($data);

        return response()->json($doctor);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $doctor->update($data);

        return response()->json([
            'message' => 'Doctor updated successfully',
            'doctor' => $doctor,
        ]);
    }
}
