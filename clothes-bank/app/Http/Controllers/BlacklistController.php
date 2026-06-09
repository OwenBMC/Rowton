<?php

namespace App\Http\Controllers;

use App\Models\Blacklist;
use App\Models\ServiceUser;
use Illuminate\Http\Request;

class BlacklistController extends Controller
{
    public function active()
    {
        return Blacklist::with('serviceUser')
            ->where(function ($q) {
                $q->whereNull('blacklist_end_date')
                    ->orWhere('blacklist_end_date', '>', now());
            })
            ->latest()
            ->get();
    }

    public function activeByUser($id)
    {
        return Blacklist::where('service_user_id', $id)
            ->whereDate('blacklist_start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('blacklist_end_date')
                    ->orWhereDate('blacklist_end_date', '>=', now());
            })
            ->latest()
            ->first();
    }

    public function eligibleUsers()
    {
        $blacklistedIds = Blacklist::where(function ($q) {
            $q->whereNull('blacklist_end_date')
                ->orWhere('blacklist_end_date', '>', now());
        })
            ->pluck('service_user_id');

        return ServiceUser::whereNotIn('id', $blacklistedIds)
            ->orderBy('first_name')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_user_id' => 'required|exists:service_users,id',
            'note' => 'nullable|string',
            'blacklist_start_date' => 'required|date',
            'blacklist_end_date' => 'nullable|date|after:blacklist_start_date',
        ]);

        return Blacklist::create($validated);
    }

    public function destroy($id)
    {
        $blacklist = Blacklist::findOrFail($id);

        $blacklist->update([
            'blacklist_end_date' => now(),
        ]);

        return response()->json([
            'message' => 'Blacklist ended',
            'data' => $blacklist,
        ]);
    }
}
