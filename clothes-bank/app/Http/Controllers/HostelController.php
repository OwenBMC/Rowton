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
}