<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceItemController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/Services/Index', [
            'services' => ServiceItem::with('category')
                ->orderBy('name')
                ->get(),

            'categories' => ServiceCategory::orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_category_id' => [
                'required',
                'exists:service_categories,id',
            ],

            'name' => [
                'required',
                'string',
            ],

            'short' => [
                'nullable',
                'string',
            ],

            'active' => [
                'boolean',
            ],
        ]);

        ServiceItem::create($validated);

        return back();
    }

    public function update(Request $request, ServiceItem $serviceItem)
    {
        $validated = $request->validate([
            'service_category_id' => [
                'required',
                'exists:service_categories,id',
            ],

            'name' => [
                'required',
                'string',
            ],

            'short' => [
                'nullable',
                'string',
            ],

            'active' => [
                'boolean',
            ],
        ]);

        $serviceItem->update($validated);

        return back();
    }

    public function updateFrequency(
        Request $request,
        ServiceItem $serviceItem
    ) {
        $validated = $request->validate([
            'frequency_days' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $serviceItem->update($validated);

        return back();
    }

    public function destroy(ServiceItem $serviceItem)
    {
        $serviceItem->delete();

        return back();
    }
}
