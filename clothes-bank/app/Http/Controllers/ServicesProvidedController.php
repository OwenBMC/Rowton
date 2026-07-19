<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Models\ServiceItem;
use App\Models\ServiceProvided;
use App\Models\ServiceUser;
use App\Services\EligibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServicesProvidedController extends Controller
{
    public function __construct(
        private EligibilityService $eligibilityService
    ) {}

    public function index(Request $request)
    {
        $date = $request->input(
            'date',
            now()->toDateString()
        );

        $todayRows = ServiceProvided::with('serviceItem.category')
            ->whereDate('attendance_date', $date)
            ->get()
            ->groupBy('service_user_id');

        $users = ServiceUser::whereIn(
            'id',
            $todayRows->keys()
        )
            ->orderBy('surname')
            ->get();

        return $users->map(function ($user) use ($todayRows) {

            $rows = $todayRows->get(
                $user->id,
                collect()
            );

            $services = [];

            foreach ($rows as $row) {

                $services[$row->service_item_id] = true;

            }

            return [
                'userId' => $user->id,
                'displayName' => $user->name,

                'services' => $services,

                'eligibility' => $this->buildEligibility($user),
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

            'lastIssued' => $this->buildLastIssued($userId),

            'eligibility' => $this->buildEligibility($user),
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
            'service_item_id' => 'required|exists:service_items,id',
            'selected' => 'required|boolean',
            'override' => 'boolean',
        ]);

        $user = ServiceUser::findOrFail(
            $validated['service_user_id']
        );

        $item = ServiceItem::with('category')
            ->findOrFail(
                $validated['service_item_id']
            );

        if (! $validated['selected']) {

            ServiceProvided::where([
                'service_user_id' => $user->id,
                'service_item_id' => $item->id,
            ])
                ->whereDate(
                    'attendance_date',
                    $validated['attendance_date']
                )
                ->delete();

            return response()->json([
                'allowed' => true,
            ]);
        }

        $result = app(EligibilityService::class)
            ->check($user, $item);

        if (! $result->allowed && ! ($validated['override'] ?? false)) {

            return response()->json([
                'allowed' => false,
                'reason' => $result->reason,
            ], 422);
        }

        ServiceProvided::create([
            'service_user_id' => $user->id,
            'service_item_id' => $item->id,
            'attendance_date' => $validated['attendance_date'],
        ]);

        return response()->json([
            'allowed' => true,
        ]);
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

    public function options()
    {
        return ServiceCategory::where('active', true)
            ->with([
                'items' => function ($query) {
                    $query->where('active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();
    }

    private function buildEligibility(ServiceUser $user): array
    {
        return ServiceItem::where('active', true)
            ->with('category')
            ->get()
            ->mapWithKeys(function ($item) use ($user) {

                $result = $this->eligibilityService
                    ->check($user, $item);

                return [
                    $item->id => [
                        'allowed' => $result->allowed,
                        'reason' => $result->reason,
                        'type' => $result->type,
                    ],
                ];

            })
            ->toArray();
    }
}
