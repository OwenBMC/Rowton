<?php

namespace Database\Seeders;

use App\Models\EligibilityRule;
use App\Models\ServiceCategory;
use App\Models\ServiceItem;
use Illuminate\Database\Seeder;

class EligibilityRuleSeeder extends Seeder
{
    public function run(): void
    {

        /*
         * Clothing:
         *
         * Eligible if:
         *
         * housing_status = rough-sleeper
         * OR
         * housing_status = sofa-surfing
         */

        $clothing = ServiceCategory::where('name', 'Clothing')
            ->first();

        if ($clothing) {

            $rule = EligibilityRule::create([
                'service_category_id' => $clothing->id,
                'name' => 'Clothing eligibility',
                'active' => true,
            ]);

            $group = $rule->groups()->create([
                'operator' => 'OR',
            ]);

            $group->conditions()->createMany([

                [
                    'attribute' => 'housing_status',
                    'operator' => '=',
                    'value' => 'rough-sleeper',
                ],

                [
                    'attribute' => 'housing_status',
                    'operator' => '=',
                    'value' => 'sofa-surfing',
                ],

            ]);

        }

        /*
         * Coats:
         *
         * Eligible if:
         *
         * housing_status != housed
         */

        $coat = ServiceItem::where('name', 'Coat')
            ->first();

        if ($coat) {

            $rule = EligibilityRule::create([
                'service_item_id' => $coat->id,
                'name' => 'Coat requires insecure housing',
                'active' => true,
            ]);

            $group = $rule->groups()->create([
                'operator' => 'AND',
            ]);

            $group->conditions()->create([

                'attribute' => 'housing_status',
                'operator' => '!=',
                'value' => 'housed',

            ]);

        }

        /*
         * Rough sleeping category:
         *
         * housing_status = rough-sleeper
         */

        $roughSleeping = ServiceCategory::where('name', 'Rough Sleeping')
            ->first();

        if ($roughSleeping) {

            $rule = EligibilityRule::create([
                'service_category_id' => $roughSleeping->id,
                'name' => 'Must be Rough Sleeper',
                'active' => true,
            ]);

            $group = $rule->groups()->create([
                'operator' => 'AND',
            ]);

            $group->conditions()->create([

                'attribute' => 'housing_status',
                'operator' => '=',
                'value' => 'rough-sleeper',

            ]);

        }

    }
}
