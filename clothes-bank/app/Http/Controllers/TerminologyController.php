<?php

namespace App\Http\Controllers;

use App\Models\Terminology;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TerminologyController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/Terminology/Index', [
            'terms' => Terminology::orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, Terminology $terminology)
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:255'],
        ]);

        $terminology->update($validated);

        return back();
    }
}
