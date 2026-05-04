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

    public function today()
    {
        $date = now()->toDateString();

        $users = ServiceUser::whereHas('attendances', function ($query) use ($date) {
            // This filters the USERS: only those with attendance today
            $query->whereDate('attendance_date', $date);
        })
            ->with([
            'attendances' => function ($query) use ($date) {
                // This filters the LOADED RELATION: ensures $user->attendances only contains today
                $query->whereDate('attendance_date', $date);
            },
        ])
            ->get();

        $result = $users->map(function ($user) {
            $attendance = $user->attendances->first();

            return [
                'attendanceId' => $attendance->id ?? null,
                'userId' => $user->id,
                'displayName' => $user->name,
                'arrival_time' => $attendance->arrival_time ?? '',
                'departure_time' => $attendance->departure_time ?? '',
                'services' => new \stdClass,
                'toiletries' => [],
                'isBlacklisted' => $user->is_blacklisted,
            ];
        })
            ->sortBy(function ($item) {
                return $item['arrival_time'] ?: '23:59';
            })
            ->values();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        $validated = $request->validate([
            'attendees' => 'required|array',
            'attendees.*.id' => 'required|integer|exists:service_users,id',
            'attendees.*.arrival_time' => 'nullable|string',
            'attendees.*.departure_time' => 'nullable|string',
        ]);

        $attendanceIds = [];

        foreach ($validated['attendees'] as $attendee) {
            $attendance = Attendance::updateOrCreate(
                [
                    'service_user_id' => $attendee['id'],
                    'attendance_date' => $date->toDateString(),
                ],
                [
                    'arrival_time' => $attendee['arrival_time'] ?: null,
                    'departure_time' => $attendee['departure_time'] ?: null,
                ]
            );

            $attendanceIds[] = [
                'service_user_id' => $attendee['id'],
                'attendance_id' => $attendance->id,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved successfully',
            'data' => $attendanceIds,
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
