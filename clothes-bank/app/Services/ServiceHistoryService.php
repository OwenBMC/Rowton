<?php

namespace App\Services;

use App\Models\ServiceItem;
use App\Models\ServiceProvided;

class ServiceHistoryService
{
    public function check(
        int $serviceUserId,
        ServiceItem $service
    ): ServiceHistoryResult {

        $category = $service->category;

        if (! $category->track_history) {
            return new ServiceHistoryResult(true);
        }

        $frequency =
            $service->frequency_days
            ?? $category->default_frequency_days;

        if (! $frequency) {
            return new ServiceHistoryResult(true);
        }

        $last = ServiceProvided::where(
            'service_user_id',
            $serviceUserId
        )
            ->where('service_item_id', $service->id)
            ->orderByDesc('attendance_date')
            ->first();

        if (! $last) {
            return new ServiceHistoryResult(true);
        }

        $daysAgo = now()
            ->diffInDays($last->attendance_date);

        if ($daysAgo >= $frequency) {
            return new ServiceHistoryResult(true);
        }

        return new ServiceHistoryResult(
            false,
            "{$service->name} was already provided {$daysAgo} days ago. It can be provided again in ".($frequency - $daysAgo).' days.',
            $frequency - $daysAgo
        );
    }
}
