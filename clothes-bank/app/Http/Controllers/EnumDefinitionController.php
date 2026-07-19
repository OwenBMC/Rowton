<?php

namespace App\Http\Controllers;

use App\Models\EnumDefinition;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnumDefinitionController extends Controller
{
    /**
     * Display all enum groups.
     */
    public function index()
    {
        return Inertia::render('settings/Enums/Index', [
            'groups' => EnumDefinition::query()
                ->select('group')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('group')
                ->orderBy('group')
                ->get(),
        ]);
    }

    /**
     * Display a single enum group.
     */
    public function show(string $group)
    {
        $enums = EnumDefinition::where('group', $group)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('settings/Enums/Show', [
            'group' => $group,
            'enums' => $enums,
        ]);
    }

    /**
     * Create a new enum value.
     */
    public function store(Request $request, string $group)
    {
        $validated = $request->validate([
            'key' => [
                'required',
                'string',
                'max:255',
            ],
            'label' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        EnumDefinition::create([
            'group' => $group,
            'key' => $validated['key'],
            'label' => $validated['label'],
            'active' => true,
            'sort_order' => EnumDefinition::where('group', $group)->max('sort_order') + 1,
        ]);

        return back();
    }

    /**
     * Update an enum value.
     */
    public function update(Request $request, EnumDefinition $enumDefinition)
    {
        $validated = $request->validate([
            'label' => [
                'required',
                'string',
                'max:255',
            ],
            'active' => [
                'required',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
            ],
        ]);

        $enumDefinition->update($validated);

        return back();
    }

    /**
     * Delete an enum value.
     */
    public function destroy(EnumDefinition $enumDefinition)
    {
        $enumDefinition->delete();

        return back();
    }
}
