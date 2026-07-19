<?php

namespace App\Services;

use App\Models\ServiceCategory;
use App\Models\ServiceItem;
use App\Models\ServiceProvided;
use App\Models\ServiceUser;
use Carbon\Carbon;

class ServiceProvisionService
{
    public function __construct(
        protected EligibilityService $eligibilityService,
    ) {}

    public function build(ServiceUser $serviceUser, Carbon|string $date): array
    {
        $date = Carbon::parse($date);

        $categories = ServiceCategory::query()
            ->where('active', true)
            ->with([
                'serviceItems' => function ($query) {
                    $query->where('active', true)
                        ->with('policy')
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        return [
            'userId' => $serviceUser->id,
            'displayName' => $serviceUser->name,

            'categories' => $categories->map(function (ServiceCategory $category) use ($serviceUser, $date) {

                return [
                    'id' => $category->id,
                    'name' => $category->name,

                    'items' => $category->serviceItems->map(function (ServiceItem $item) use ($serviceUser, $date) {

                        $selected = ServiceProvided::query()
                            ->whereDate('attendance_date', $date)
                            ->where('service_user_id', $serviceUser->id)
                            ->where('service_item_id', $item->id)
                            ->exists();

                        $lastIssued = ServiceProvided::query()
                            ->where('service_user_id', $serviceUser->id)
                            ->where('service_item_id', $item->id)
                            ->whereDate('attendance_date', '<', $date)
                            ->latest('attendance_date')
                            ->first();

                        $daysAgo = null;

                        if ($lastIssued) {
                            $daysAgo = Carbon::parse($lastIssued->attendance_date)
                                ->diffInDays($date);
                        }

                        $eligibility = $this->eligibilityService
                            ->evaluate($serviceUser, $item);

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'short' => $item->short,

                            'selected' => $selected,

                            'eligible' => $eligibility->eligible,

                            'overrideRequired' => ! $eligibility->eligible,

                            'reason' => implode(', ', $eligibility->reasons),

                            'lastIssued' => [
                                'date' => $lastIssued?->attendance_date,
                                'daysAgo' => $daysAgo,
                            ],
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }
}
