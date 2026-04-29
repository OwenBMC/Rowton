<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    // GET /api/practices
    public function index()
    {
        return Practice::orderBy('name')->get();
    }

    // POST /api/practices
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $practice = Practice::create($data);

        return response()->json($practice);
    }

    public function update(Request $request, Practice $practice)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:30',
            'email' => 'nullable|string|',
        ]);

        $practice->update($data);

        return response()->json([
            'message' => 'Practice updated successfully',
            'practice' => $practice,
        ]);
    }
}
