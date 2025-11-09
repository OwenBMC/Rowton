<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceUser;
use Carbon\Carbon;
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
            }
        ])->get();

        // Transform for Inertia
        $props = $serviceUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'isBlacklisted' => $user->blacklist->isNotEmpty(),
                'attendances' => $user->attendances->map(fn($a) => [
                    'arrival_time' => $a->arrival_time,
                    'departure_time' => $a->departure_time,
                ]),
                'services_provided' => $user->servicesProvided->map(fn($s) => [
                    'service_name' => $s->service_name,
                    'category' => $s->category,
                    'code' => $s->code ?? null,
                ]),
            ];
        });

        return Inertia::render('Attendance', [
            'serviceUsers' => $props,
            'date' => $carbonDate->toDateString(),
        ]);
    }
}
