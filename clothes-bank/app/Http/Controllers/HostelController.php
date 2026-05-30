<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    /**
     * Display a listing of hostels for dropdown selects.
     */
    public function index()
    {
        // We order by name so the dropdown is easy to navigate on tablets
        return response()->json(
            Hostel::orderBy('name')->get(['id', 'name', 'city', 'primary_client_group'])
        );
    }

    /**
     * Display the specified hostel (useful for auto-filling address details).
     */
    public function show(Hostel $hostel)
    {
        return response()->json($hostel);
    }

    public function update(Request $request, Hostel $hostel)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $data['country'] = $data['country'] ?? 'UK';
        $hostel->update($data);

        return response()->json([
            'message' => 'Hostel updated successfully',
            'hostel' => $hostel,
        ]);
    }
}
