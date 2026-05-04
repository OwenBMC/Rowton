<?php

namespace App\Http\Controllers;

use App\Models\BlackListed;
use App\Models\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ServiceUserController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceUser::query();

        // SEARCH (name / contact)
        if ($request->search) {
            $search = strtolower($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(middle_names) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(surname) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(contact_number) LIKE ?', ["%{$search}%"]);
            });
        }

        // FILTER: housing
        if ($request->housing_status) {
            $query->where('housing_status', $request->housing_status);
        }

        // FILTER: blacklisted (using your relationship)
        if ($request->blacklisted === 'true') {
            $query->whereHas('blacklist');
        }

        if ($request->blacklisted === 'false') {
            $query->whereDoesntHave('blacklist');
        }

        return $query
            ->with(['blacklist'])
            ->orderBy('surname')
            ->paginate(20);
    }

    public function fullIndex()
    {
        return ServiceUser::with([
            'registration',
            'blacklist',
        ])->get();
    }

    public function show(ServiceUser $service_user)
    {
        $service_user->load([
            'registration',
            'nextOfKin',
            'attendances',
            'servicesProvided',
            'doctors.practice',
        ]);

        return response()->json([
            'service_user' => $service_user,
        ]);
    }

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
        if (empty($request->first_name) && empty($request->middle_names) && empty($request->surname) && empty($request->nickname)) {
            return response()->json([
                'message' => 'At least one of first name, middle names, surname, or nickname is required',
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

    public function update(Request $request, ServiceUser $serviceUser)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_names' => 'nullable|string|max:255',
            'surname' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',

            'contact_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:255',

            'housing_status' => 'nullable|string|max:255',
            'food_allergies' => 'nullable|boolean',

            'is_blacklisted' => 'nullable|boolean',
        ]);

        if ($data->first()->is_blacklisted) {
            $blacklist_row = [
                'service_user_id' => $data->id,
                'blacklist_start_date' => now()->toDateString(),
            ];
            BlackListed->update();
        }
        $serviceUser->update($data);

        return response()->json([
            'message' => 'Service user updated',
            'user' => $serviceUser->load(['blacklist']),
        ]);
    }

    public function delete(Request $request, ServiceUser $serviceUser)
    {
        $serviceUser->delete($serviceUser->id);
    }
}
