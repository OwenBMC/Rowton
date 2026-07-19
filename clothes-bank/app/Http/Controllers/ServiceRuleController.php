<?php

namespace App\Http\Controllers;

use App\Models\ServiceRule;
use Illuminate\Http\Request;

class ServiceRuleController extends Controller
{
    public function store(Request $request)
    {
        return ServiceRule::create(
            $request->validate([
                'service_item_id' => 'required|exists:service_items,id',
                'attribute' => 'required',
                'operator' => 'required',
                'value' => 'required',
            ])
        );
    }

    public function destroy(ServiceRule $serviceRule)
    {
        $serviceRule->delete();

        return response()->noContent();
    }
}
