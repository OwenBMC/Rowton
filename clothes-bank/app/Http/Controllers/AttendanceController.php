<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ServiceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $carbonDate = Carbon::parse($date);

        // Eager load related models
        $serviceUsers = ServiceUser::with([
            'attendances' => function ($query) use ($carbonDate) {
                // Only get attendances for the given date
                $query->whereDate('attendance_date', $carbonDate);
            },
            'servicesProvided' => function ($query) use ($carbonDate) {
                // Only get services for the given date
                $query->whereDate('attendance_date', $carbonDate);
            },
            'blacklist' => function ($query) use ($carbonDate) {
                $query->whereDate('blacklist_start_date', '<=', $carbonDate)
                    ->whereDate('blacklist_end_date', '>=', $carbonDate);
            },
        ])->get();

        // Transform for Inertia
        $props = $serviceUsers = ServiceUser::with(['attendances', 'services_provided'])->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'isBlacklisted' => $user->is_blacklisted,
                'attendances' => $user->attendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->id, // this is crucial
                        'arrival_time' => $attendance->arrival_time,
                        'departure_time' => $attendance->departure_time,
                    ];
                }),
                'services_provided' => $user->services_provided,
            ];
        });

        return Inertia::render('Attendance', [
            'serviceUsers' => $props,
            'date' => $carbonDate->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        // Validate that every attendee has a proper service_user_id
        $validated = $request->validate([
            'attendees' => 'required|array',
            'attendees.*.id' => 'required|integer|exists:service_users,id',
            'attendees.*.arrival_time' => 'nullable|string',
            'attendees.*.departure_time' => 'nullable|string',
        ]);

        foreach ($validated['attendees'] as $attendee) {
            // Create or update attendance for that user and date
            Attendance::updateOrCreate(
                [
                    'service_user_id' => $attendee['id'],
                    'attendance_date' => $date->toDateString(),
                ],
                [
                    'arrival_time' => $attendee['arrival_time'] ?: null,
                    'departure_time' => $attendee['departure_time'] ?: null,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved successfully',
        ]);
    }

    public function update(Request $request)
    {

        $attendance = Attendance::findOrFail($request->id);

        $updateData = [];

        if ($request->has('arrival_time')) {
            $updateData['arrival_time'] = $request->arrival_time; // can be null
        }

        if ($request->has('departure_time')) {
            $updateData['departure_time'] = $request->departure_time; // can be null
        }

        $attendance->update($updateData);

        return response()->json([
            'success' => true,
            'attendance' => $attendance,
        ]);
    }
}
