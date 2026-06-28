<?php

namespace App\Http\Controllers;

use App\Models\ServiceProvided;
use App\Models\ServiceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServicesProvidedController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        /**
         * Get ALL rows for the selected day once.
         * This is the single source of truth.
         */
        $todayRows = ServiceProvided::whereDate('attendance_date', $date)->get();

        /**
         * Group once by user for fast lookup.
         */
        $todayByUser = $todayRows->groupBy('service_user_id');

        /**
         * History used for lastIssued calculation.
         */
        $history = ServiceProvided::orderByDesc('attendance_date')
            ->get()
            ->groupBy('service_user_id');

        /**
         * We still restrict to users who have data today (your current design choice).
         * If you later want ALL users, this is where you'd change it.
         */
        $todayUserIds = $todayByUser->keys();

        $users = ServiceUser::whereIn('id', $todayUserIds)
            ->get()
            ->sortBy(fn ($u) => $u->name ?? '')
            ->values();

        return $users->map(function ($user) use ($todayByUser, $history) {

            $userTodayRows = $todayByUser->get($user->id, collect());

            /**
             * Build clothing map (checkbox state)
             */
            $services = $this->buildServicesMap($userTodayRows);

            /**
             * Toiletries list
             */
            $toiletriesMap = collect([
                ['label' => 'Brush/comb', 'short' => 'B/CB'],
                ['label' => 'Conditioner', 'short' => 'C'],
                ['label' => 'Deodorant', 'short' => 'D'],
                ['label' => 'Sanitary Products', 'short' => 'San'],
                ['label' => 'Shampoo', 'short' => 'SH'],
                ['label' => 'Shower Gel', 'short' => 'SG'],
                ['label' => 'Soap', 'short' => 'S'],
                ['label' => 'Toothbrush', 'short' => 'TB'],
                ['label' => 'Toothpaste', 'short' => 'TP'],
                ['label' => 'Wipes', 'short' => 'W'],
            ]);

            $toiletries = $userTodayRows
                ->where('service_category', 'toiletries')
                ->map(function ($r) use ($toiletriesMap) {

                    return $toiletriesMap->firstWhere('short', $r->service_name);

                })
                ->filter()
                ->values();

            /**
             * Last issued lookup (unchanged logic, but stable source)
             */
            $lastIssued = [];

            foreach ($this->clothingOptions() as $item) {

                $previousIssue = $history
                    ->get($user->id, collect())
                    ->first(function ($record) use ($item) {

                        return $record->service_category === 'clothing'
                            && $record->service_name === $item;
                    });

                if ($previousIssue) {

                    $previousIssueDate = Carbon::parse($previousIssue->attendance_date);
                    $days = $previousIssueDate->diffInDays(now());

                    $lastIssued[$item] = [
                        'date' => $previousIssueDate->toDateString(),
                        'display' => $previousIssueDate->format('d M Y'),
                        'daysAgo' => match (true) {
                            $days < 1 => 'Today',
                            $days < 2 => 'Yesterday',
                            default => (int) floor($days).' days ago',
                        },
                    ];
                } else {
                    $lastIssued[$item] = null;
                }
            }

            return [
                'userId' => $user->id,
                'displayName' => $user->name,

                'services' => $services,

                'toiletries' => $toiletries,

                'lastIssued' => $lastIssued,
            ];
        });
    }

    public function show(Request $request, $userId)
    {
        $date = $request->date ?? now()->toDateString();

        $user = ServiceUser::findOrFail($userId);

        $today = ServiceProvided::where('service_user_id', $userId)
            ->whereDate('attendance_date', $date)
            ->get();

        return [
            'userId' => $user->id,
            'displayName' => $user->name,
            'services' => $this->buildServicesMap($today),
            'toiletries' => $today->where('service_category', 'toiletries')
                ->pluck('service_name')
                ->values(),

            'lastIssued' => $this->buildLastIssued($userId),
        ];
    }

    private function clothingOptions(): array
    {
        return [
            'Coat',
            'Hoodie',
            'Sweat-shirt',
            'Tee-shirt',
            'Top',
            'Tracksuit bottoms',
            'Jeans',
            'Shoes',
            'Socks',
            'Underwear',
            'Hats',
            'Scarves',
            'Gloves',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_user_id' => 'required|exists:service_users,id',
            'attendance_date' => 'required|date',
            'services' => 'array',
            'toiletries' => 'array',
        ]);

        \App\Models\ServiceProvided::query()
            ->where('service_user_id', $validated['service_user_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->delete();

        foreach ($validated['services'] ?? [] as $service => $selected) {

            if (! $selected) {
                continue;
            }

            \App\Models\ServiceProvided::create([
                'service_user_id' => $validated['service_user_id'],
                'attendance_date' => $validated['attendance_date'],
                'service_category' => 'clothing',
                'service_name' => $service,
            ]);
        }

        foreach ($validated['toiletries'] ?? [] as $toiletry) {

            \App\Models\ServiceProvided::create([
                'service_user_id' => $validated['service_user_id'],
                'attendance_date' => $validated['attendance_date'],
                'service_category' => 'toiletries',
                'service_name' => $toiletry['short'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function attendees()
    {
        $today = now()->toDateString();

        return \App\Models\Attendance::query()
            ->with('serviceUser')
            ->whereDate('attendance_date', $today)
            ->whereNull('departure_time')
            ->get()
            ->groupBy('service_user_id')
            ->map(function ($records) {
                $latest = $records->sortByDesc('arrival_time')->first();

                return [
                    'id' => $latest->serviceUser->id,
                    'name' => $latest->serviceUser->name,
                ];
            })
            ->values();
    }

    private function buildServicesMap($records): array
    {
        $map = [];

        foreach ($records as $record) {

            if ($record->service_category !== 'clothing') {
                continue;
            }

            $map[$record->service_name] = true;
        }

        return $map;
    }

    private function buildLastIssued(int $userId): array
    {
        $records = ServiceProvided::where('service_user_id', $userId)
            ->orderByDesc('attendance_date')
            ->get();

        $lastIssued = [];

        foreach ($this->clothingOptions() as $item) {

            $match = $records->first(function ($record) use ($item) {

                return $record->service_category === 'clothing'
                    && $record->service_name === $item;
            });

            if (! $match) {
                $lastIssued[$item] = null;

                continue;
            }

            $date = Carbon::parse($match->attendance_date);
            $days = $date->diffInDays(now());

            $lastIssued[$item] = [
                'date' => $date->toDateString(),
                'display' => $date->format('d M Y'),
                'daysAgo' => match (true) {
                    $days < 1 => 'Today',
                    $days < 2 => 'Yesterday',
                    default => (int) floor($days).' days ago',
                },
            ];
        }

        return $lastIssued;
    }
}
