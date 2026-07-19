<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'short' => 'required|string|unique:service_categories,short',
            'management_type' => 'required|string',
            'active' => 'boolean',
        ]);

        ServiceCategory::create($validated);

        return back();
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'short' => 'required|string',
            'management_type' => 'required|string',
            'active' => 'boolean',
        ]);

        $serviceCategory->update($validated);

        return back();
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();

        return back();
    }
}
