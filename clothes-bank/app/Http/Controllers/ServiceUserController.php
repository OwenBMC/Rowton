<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceUser;
use App\Models\BlackListed;
use App\Models\ServicesProvided;
use App\Models\Attendance;

use Illuminate\Support\Carbon;

class ServiceUserController extends Controller
{
    public static function allUsers()
{
    $users = ServiceUser::select('id', 'name', 'nickname')->get();

    return inertia('AttendanceDropdown', [ // or just return JSON if called via API
        'serviceUsers' => $users,
    ]);
}
public function allUsersJson()
{
    $users = ServiceUser::select('id', 'name', 'nickname')->get();
    
    return response()->json($users);
}
    public static function getAttendance()
    {
        // Parse the date from request or default to today
        $date = Carbon::parse( now()->toDateString());

        // Eager-load relationships filtered by the given date
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
            }
        ])->get();

        // Transform for frontend
        $payload = $serviceUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'isBlacklisted' => $user->blacklist ? true : false,
                'attendances' => $user->attendances,
                'services_provided' => $user->servicesProvided,
            ];
        });

        return inertia('Attendance', [
            'serviceUsers' => $serviceUsers->filter(function ($user) {
                return $user->attendances->count() > 0;
            })->values(), // ->values() resets the collection keys
            'date' => $date->toDateString(),
        ]);
    }

}
