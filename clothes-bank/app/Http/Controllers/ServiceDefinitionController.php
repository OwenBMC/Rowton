<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceDefinitionController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/Services/Index', [
            'services' => Service::orderBy('category')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'key' => ['required', 'string', 'unique:services,key'],
            'category' => ['required', 'string'],
            'input_type' => ['required', 'string'],
            'active' => ['boolean'],
        ]);

        Service::create($validated);

        return back();
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['required', 'string'],
            'input_type' => ['required', 'string'],
            'active' => ['boolean'],
        ]);

        $service->update($validated);

        return back();
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return back();
    }
}
