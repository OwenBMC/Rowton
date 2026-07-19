<?php

namespace App\Http\Controllers;

use App\Models\ServiceItem;
use Illuminate\Http\Request;

class ServicePolicyController extends Controller
{
    public function update(Request $request, ServiceItem $serviceItem)
    {
        return $serviceItem->policy()->updateOrCreate(
            [],
            $request->validate([
                'cooldown_days' => 'nullable|integer|min:0',
                'manager_override' => 'boolean',
                'enabled' => 'boolean',
            ])
        );
    }
}
