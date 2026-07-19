<?php

namespace App\Services;

use App\Models\ServiceItem;
use App\Models\ServiceProvided;
use App\Models\ServiceUser;
use Carbon\Carbon;

class EligibilityService
{
    public function evaluate(
        ServiceUser $serviceUser,
        ServiceItem $serviceItem
    ): EligibilityResult {

        $reasons = [];
        if ($serviceItem->category->track_history) {

            $lastIssued = ServiceProvided::where(
                'service_user_id',
                $serviceUser->id
            )
                ->where('service_item_id', $serviceItem->id)
                ->latest('attendance_date')
                ->first();

            if ($lastIssued) {

                $daysSince = Carbon::parse(
                    $lastIssued->attendance_date
                )->diffInDays(now());

                if ($daysSince < $serviceItem->category->frequency_days) {

                    return EligibilityResult::deny(
                        "This service was already provided {$daysSince} days ago. It can be provided every {$serviceItem->category->frequency_days} days."
                    );

                }
            }
        }
        /*
         * Check service policy rules
         */

        if ($serviceItem->policy) {

            $policy = $serviceItem->policy;

            if ($policy->minimum_days_between) {

                $lastIssued = $serviceUser
                    ->servicesProvided()
                    ->where('service_item_id', $serviceItem->id)
                    ->latest('attendance_date')
                    ->first();

                if ($lastIssued) {

                    $daysAgo = Carbon::parse(
                        $lastIssued->attendance_date
                    )->diffInDays(now());

                    if ($daysAgo < $policy->minimum_days_between) {

                        $remaining =
                            $policy->minimum_days_between - $daysAgo;

                        $reasons[] =
                            "Available again in {$remaining} days";

                    }
                }
            }
        }

        /*
         * Check eligibility rules
         */

        foreach ($serviceItem->rules as $rule) {

            if (! $this->ruleMatches($serviceUser, $rule)) {

                $reasons[] = $rule->name;

            }

        }

        return new EligibilityResult(
            eligible: count($reasons) === 0,
            reasons: $reasons,
        );
    }

    protected function ruleMatches(
        ServiceUser $serviceUser,
        $rule
    ): bool {

        /*
         * Rules contain groups.
         *
         * Example:
         *
         * Group 1 (OR)
         *     housing_status = rough-sleeper
         *     housing_status = sofa-surfing
         *
         */

        foreach ($rule->groups as $group) {

            $results = [];

            foreach ($group->conditions as $condition) {

                $results[] = $this->conditionMatches(
                    $serviceUser,
                    $condition
                );

            }

            if ($group->operator === 'OR') {

                if (collect($results)->contains(true)) {
                    return true;
                }

            } else {

                if (collect($results)->every(fn ($value) => $value)) {
                    return true;
                }

            }

        }

        return false;
    }

    public function check(ServiceUser $user, ServiceItem $item): EligibilityResult
    {
        /*
         * Get rules attached directly to the item
         * and rules inherited from the category.
         */
        $rules = collect()
            ->merge($item->eligibilityRules)
            ->merge($item->category?->eligibilityRules ?? []);

        foreach ($rules as $rule) {

            foreach ($rule->conditionGroups as $group) {

                $groupPassed = true;

                foreach ($group->conditions as $condition) {

                    if (! $this->conditionMatches($user, $condition)) {

                        $groupPassed = false;
                        break;
                    }
                }

                if (! $groupPassed) {

                    return new EligibilityResult(
                        false,
                        "{$rule->name}: eligibility conditions failed.",
                        'rule'
                    );
                }
            }
        }

        /*
         * History checks
         */
        if ($item->category?->track_history) {

            $last = ServiceProvided::where('service_user_id', $user->id)
                ->where('service_item_id', $item->id)
                ->latest('attendance_date')
                ->first();

            if ($last) {

                $lastDate = Carbon::parse($last->attendance_date)->startOfDay();
                $today = now()->startOfDay();

                $days = $lastDate->diffInDays($today);

                $frequency = $item->frequency_days
                    ?? $item->category->default_frequency_days;

                // Issued today - don't show as a restriction
                if ($days < 1) {
                    return new EligibilityResult(true, '');
                }

                if ($frequency && $days < $frequency) {

                    return new EligibilityResult(
                        false,
                        "{$item->name} was issued {$days} days ago. Available again in ".
                        ($frequency - $days).
                        ' days.',
                        'frequency'
                    );
                }
            }
        }

        return new EligibilityResult(true, '');
    }

    private function conditionMatches(
        ServiceUser $user,
        $condition
    ): bool {

        $attribute = $condition->attribute;

        $actual = $user->{$attribute};

        return match ($condition->operator) {

            '=' => $actual == $condition->value,

            '!=' => $actual != $condition->value,

            'in' => in_array(
                $actual,
                json_decode($condition->value, true)
            ),

            default => true,
        };
    }
}
