<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from') ?? now()->startOfMonth()->toDateString();
        $to = $request->input('to') ?? now()->toDateString();

        $base = collect();
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $base->push($date->toDateString());
        }

        if (! $from || ! $to) {
            $from = now()->startOfMonth()->toDateString();
            $to = now()->toDateString();
        }

        $period = CarbonPeriod::create($from, $to);

        $labels = collect($period)->map(fn ($d) => $d->format('Y-m-d'));

        $query = DB::table('attendances')
            ->join('service_users', 'service_users.id', '=', 'attendances.service_user_id')
            ->when($from, fn ($q) => $q->whereDate('attendances.attendance_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('attendances.attendance_date', '<=', $to));

        // ---------------------------
        // FILTERS
        // ---------------------------

        if ($request->filled('gender')) {
            $query->where('service_users.gender', $request->gender);
        }

        if ($request->filled('housing_status')) {
            $query->where('service_users.housing_status', $request->housing_status);
        }

        if ($request->filled('registered')) {

            if ($request->registered === 'registered') {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('registrations')
                        ->whereColumn('registrations.service_user_id', 'service_users.id');
                });
            }

            if ($request->registered === 'unregistered') {
                $query->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('registrations')
                        ->whereColumn('registrations.service_user_id', 'service_users.id');
                });
            }
        }

        if ($request->filled('service_user_ids')) {
            $query->whereIn('service_users.id', $request->service_user_ids);
        }

        // ---------------------------
        // TOTAL MODE (DEFAULT)
        // ---------------------------

        $mode = $request->input('chart_mode', 'total');

        $series = [];

        if ($mode === 'total') {

            $raw = (clone $query)
                ->select(
                    'attendances.attendance_date',
                    DB::raw('COUNT(DISTINCT service_users.id) as value')
                )
                ->groupBy('attendances.attendance_date')
                ->pluck('value', 'attendances.attendance_date');

            $filled = $labels->mapWithKeys(fn ($date) => [
                $date => $raw[$date] ?? 0,
            ]);

            $series[] = [
                'name' => 'Total',
                'data' => $filled->values(),
            ];
        }

        // ---------------------------
        // GENDER STACKED
        // ---------------------------

        if ($mode === 'gender') {

            $rows = (clone $query)
                ->select(
                    'attendances.attendance_date',
                    'service_users.gender',
                    DB::raw('COUNT(DISTINCT service_users.id) as value')
                )
                ->groupBy('attendances.attendance_date', 'service_users.gender')
                ->get();

            $grouped = $rows->groupBy('gender');

            foreach ($grouped as $gender => $items) {

                $map = $items->pluck('value', 'attendance_date');

                $filled = $labels->map(fn ($date) => $map[$date] ?? 0);

                $series[] = [
                    'name' => $gender ?? 'unknown',
                    'data' => $filled->values(),
                ];
            }
        }

        // ---------------------------
        // HOUSING STACKED
        // ---------------------------

        if ($mode === 'housing') {

            $rows = (clone $query)
                ->select(
                    'attendances.attendance_date',
                    'service_users.housing_status',
                    DB::raw('COUNT(DISTINCT service_users.id) as value')
                )
                ->groupBy('attendances.attendance_date', 'service_users.housing_status')
                ->get();

            $grouped = $rows->groupBy('housing_status');

            foreach ($grouped as $status => $items) {

                $map = $items->pluck('value', 'attendance_date');

                $filled = $labels->map(fn ($date) => $map[$date] ?? 0);

                $series[] = [
                    'name' => $status ?? 'unknown',
                    'data' => $filled->values(),
                ];
            }
        }

        if ($mode === 'registration') {

            $rows = DB::table('attendances')
                ->join('service_users', 'service_users.id', '=', 'attendances.service_user_id')
                ->leftJoin('registrations', 'registrations.service_user_id', '=', 'service_users.id')
                ->when($from, fn ($q) => $q->whereDate('attendances.attendance_date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('attendances.attendance_date', '<=', $to))
                ->select(
                    'attendances.attendance_date',
                    DB::raw("CASE 
                WHEN registrations.id IS NULL THEN 'unregistered'
                ELSE 'registered'
            END as reg_status"),
                    DB::raw('COUNT(DISTINCT service_users.id) as value')
                )
                ->groupBy('attendances.attendance_date', 'reg_status')
                ->get();

            $grouped = $rows->groupBy('reg_status');

            $series = $grouped->map(function ($items, $status) use ($base) {

                $map = $items->pluck('value', 'attendance_date')->toArray();

                return [
                    'name' => $status,
                    'data' => array_map(fn ($d) => $map[$d] ?? 0, $base->toArray()),
                ];
            })->values()->toArray();
        }

        // ---------------------------
        // SUMMARY
        // ---------------------------

        $summary = [
            'total_unique_attendees' => (clone $query)
                ->distinct('service_users.id')
                ->count('service_users.id'),
        ];

        return response()->json([
            'labels' => $labels->values(),
            'series' => $series,
            'summary' => $summary,
        ]);
    }
}
