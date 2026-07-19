<?php

namespace App\Http\Controllers;

use App\Models\EligibilityRule;
use App\Models\EnumDefinition;
use App\Models\ServiceCategory;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EligibilityRuleController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/Rules/Index', [

            'rules' => EligibilityRule::with([
                'serviceItem',
                'category',
                'conditionGroups.conditions',
            ])->get(),

            'services' => ServiceItem::where('active', true)
                ->orderBy('name')
                ->get(),

            'trackedServices' => ServiceItem::with('category')
                ->where('active', true)
                ->whereHas('category', function ($query) {
                    $query->where('track_history', true);
                })
                ->orderBy('name')
                ->get(),

            'categories' => ServiceCategory::where('active', true)
                ->orderBy('name')
                ->get(),

            'attributes' => $this->attributes(),

            'enums' => EnumDefinition::all()
                ->groupBy('group'),

        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
            ],

            'service_item_id' => [
                'nullable',
                'exists:service_items,id',
            ],

            'service_category_id' => [
                'nullable',
                'exists:service_categories,id',
            ],

            'groups' => [
                'required',
                'array',
            ],

            'groups.*.operator' => [
                'required',
                'in:AND,OR',
            ],

            'groups.*.conditions' => [
                'required',
                'array',
            ],

            'groups.*.conditions.*.attribute' => [
                'required',
                'string',
            ],

            'groups.*.conditions.*.operator' => [
                'required',
                'string',
            ],

            'groups.*.conditions.*.value' => [
                'required',
            ],

        ]);

        $rule = EligibilityRule::create([
            'name' => $validated['name'],
            'service_item_id' => $validated['service_item_id'] ?? null,
            'service_category_id' => $validated['service_category_id'] ?? null,
        ]);

        $this->storeGroups(
            $rule,
            $validated['groups']
        );

        return back();
    }

    public function update(
        Request $request,
        EligibilityRule $eligibilityRule
    ) {

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
            ],

            'service_item_id' => [
                'nullable',
                'exists:service_items,id',
            ],

            'service_category_id' => [
                'nullable',
                'exists:service_categories,id',
            ],

            'groups' => [
                'required',
                'array',
            ],

        ]);

        $eligibilityRule->update([
            'name' => $validated['name'],
            'service_item_id' => $validated['service_item_id'] ?? null,
            'service_category_id' => $validated['service_category_id'] ?? null,
        ]);

        $eligibilityRule
            ->groups()
            ->delete();

        $this->storeGroups(
            $eligibilityRule,
            $validated['groups']
        );

        return back();
    }

    private function storeGroups(
        EligibilityRule $rule,
        array $groups
    ) {

        foreach ($groups as $groupData) {

            $group = $rule->groups()->create([

                'operator' => $groupData['operator'],

            ]);

            foreach (
                $groupData['conditions'] as $condition
            ) {

                $group->conditions()->create([

                    'attribute' => $condition['attribute'],

                    'operator' => $condition['operator'],

                    'value' => $condition['value'],

                ]);

            }
        }
    }

    public function destroy(
        EligibilityRule $eligibilityRule
    ) {
        $eligibilityRule->delete();

        return back();
    }

    private function attributes()
    {
        return [

            'housing_status' => [

                'label' => 'Housing Status',

                'type' => 'enum',

                'operators' => [
                    '=',
                    '!=',
                ],

                'values' => $this->enumValues(
                    'housing_status'
                ),

            ],

            'gender' => [

                'label' => 'Gender',

                'type' => 'enum',

                'operators' => [
                    '=',
                    '!=',
                ],

                'values' => $this->enumValues(
                    'gender'
                ),

            ],

            'fda_status' => [

                'label' => 'FDA Status',

                'type' => 'enum',

                'operators' => [
                    '=',
                    '!=',
                ],

                'values' => $this->enumValues(
                    'fda_status'
                ),

            ],

            'dob' => [

                'label' => 'Date of Birth',

                'type' => 'date',

                'operators' => [
                    '=',
                    '!=',
                    '>',
                    '<',
                ],

                'values' => [],

            ],

            'registration_status' => [

                'label' => 'Registration Status',

                'type' => 'boolean',

                'operators' => [
                    '=',
                    '!=',
                ],

                'values' => [

                    [
                        'key' => 'yes',
                        'label' => 'Registered',
                    ],

                    [
                        'key' => 'no',
                        'label' => 'Not Registered',
                    ],

                ],

            ],

        ];
    }

    private function enumValues(string $group)
    {
        return EnumDefinition::where('group', $group)

            ->where('active', true)

            ->orderBy('label')

            ->get([
                'key',
                'label',
            ]);
    }
}
