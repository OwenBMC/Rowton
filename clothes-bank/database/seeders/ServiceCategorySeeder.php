<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Clothing',
                'short' => 'clothing',
                'management_type' => 'checkbox',
                'active' => true,
            ],
            [
                'name' => 'Toiletries',
                'short' => 'toiletries',
                'management_type' => 'multiselect',
                'active' => true,
            ],
            [
                'name' => 'Rough Sleeping',
                'short' => 'rough-sleeping',
                'management_type' => 'checkbox',
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['short' => $category['short']],
                $category
            );
        }
    }
}
