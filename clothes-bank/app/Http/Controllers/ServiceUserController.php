<?php

namespace App\Http\Controllers;

use App\Models\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

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

    public function getUnregistered()
    {
        $users = ServiceUser::doesntHave('registration')
            ->get(['id', 'first_name', 'middle_names', 'surname', 'nickname'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'middle_names' => $user->middle_names,
                'surname' => $user->surname,
            ]);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        // Validate: at least one name field must be present
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'middle_names' => 'nullable|string|max:255',
            'surname' => 'nullable|string|max:255',
            'nickname' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ensure at least one name field is present
        if (empty($request->first_name) && empty($request->middle_names) && empty($request->surname)) {
            return response()->json([
                'message' => 'At least one of first name, middle names, or surname is required',
            ], 422);
        }

        // Create the service user
        $serviceUser = ServiceUser::create([
            'first_name' => $request->first_name,
            'middle_names' => $request->middle_names,
            'surname' => $request->surname,
            'nickname' => $request->nickname,
        ]);

        return response()->json([
            'id' => $serviceUser->id,
            'name' => $serviceUser->name, // uses accessor
            'nickname' => $serviceUser->nickname,
        ]);
    }
}
