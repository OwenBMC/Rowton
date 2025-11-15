<?php

namespace App\Http\Controllers;

use App\Models\ServiceUser;
use Illuminate\Support\Carbon;

class ServiceUserController extends Controller
{
    public static function allUsers()
    {
        // Fetch columns needed for the accessor + nickname
        $users = ServiceUser::select('id', 'first_name', 'middle_names', 'surname', 'nickname')->get();

        return inertia('AttendanceDropdown', [
            'serviceUsers' => $users,
        ]);
    }

    public function allUsersJson()
    {
        $users = ServiceUser::select('id', 'first_name', 'middle_names', 'surname', 'nickname')->get();

        return response()->json($users);
    }

    public static function getAttendance()
    {
        $date = Carbon::parse(now()->toDateString());

        // Eager-load relationships filtered by date
        $serviceUsers = ServiceUser::with([
            'attendances' => function ($query) use ($date) {
                $query->whereDate('attendance_date', $date);
            },
            'servicesProvided' => function ($query) use ($date) {
                $query->whereDate('attendance_date', $date);
            },
            'blacklist' => function ($query) use ($date) {
                $query->where('blacklist_start_date', '<=', $date)
                    ->where('blacklist_end_date', '>=', $date);
            },
        ])->select('id', 'first_name', 'middle_names', 'surname', 'nickname') // include columns for accessor
            ->get();

        return inertia('Attendance', [
            'serviceUsers' => $serviceUsers->filter(fn ($user) => $user->attendances->count() > 0)
                ->values(),
            'date' => $date->toDateString(),
        ]);
    }
}
