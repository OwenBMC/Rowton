<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ServiceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $users = ServiceUser::whereHas('attendances', function ($query) use ($date) {
            $query->whereDate('attendance_date', $date);
        })
            ->with([
                'attendances' => function ($query) use ($date) {
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
        $request->validate([
            'id' => 'required|exists:attendances,id',
            'date' => 'required|date',
            'arrival_time' => 'nullable',
            'departure_time' => 'nullable',
        ]);

        $attendance = Attendance::where('id', $request->id)
            ->whereDate('attendance_date', $request->date)
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'Attendance record not found for this date.',
            ], 404);
        }

        $attendance->update([
            'arrival_time' => $request->arrival_time,
            'departure_time' => $request->departure_time,
        ]);

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }
}
