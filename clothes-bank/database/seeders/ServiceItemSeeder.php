<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\ServiceItem;
use Illuminate\Database\Seeder;

class ServiceItemSeeder extends Seeder
{
    public function run(): void
    {
        $clothing = ServiceCategory::where('short', 'clothing')->first();
        $roughSleeping = ServiceCategory::where('short', 'rough-sleeping')->first();

        $toiletries = ServiceCategory::where('short', 'toiletries')->first();

        $clothingItems = [
            [
                'name' => 'Coat',
                'short' => 'coat',
            ],
            [
                'name' => 'Hoodie',
                'short' => 'hoodie',
            ],
            [
                'name' => 'Sweat-shirt',
                'short' => 'sweatshirt',
            ],
            [
                'name' => 'Tee-shirt',
                'short' => 'tee-shirt',
            ],
            [
                'name' => 'Top',
                'short' => 'top',
            ],
            [
                'name' => 'Tracksuit bottoms',
                'short' => 'tracksuit-bottoms',
            ],
            [
                'name' => 'Jeans',
                'short' => 'jeans',
            ],
            [
                'name' => 'Shoes',
                'short' => 'shoes',
            ],
            [
                'name' => 'Socks',
                'short' => 'socks',
            ],
            [
                'name' => 'Underwear',
                'short' => 'underwear',
            ],
            [
                'name' => 'Hats',
                'short' => 'hats',
            ],
            [
                'name' => 'Scarves',
                'short' => 'scarves',
            ],
            [
                'name' => 'Gloves',
                'short' => 'gloves',
            ],
        ];

        foreach ($clothingItems as $item) {
            ServiceItem::updateOrCreate(
                [
                    'short' => $item['short'],
                ],
                [
                    ...$item,
                    'service_category_id' => $clothing->id,
                    'active' => true,
                ]
            );
        }
        $roughSleepingItems = [
            [
                'name' => 'Tent',
                'short' => 'tent',
            ],
        ];

        foreach ($roughSleepingItems as $item) {
            ServiceItem::updateOrCreate(
                [
                    'short' => $item['short'],
                ],
                [
                    ...$item,
                    'service_category_id' => $roughSleeping->id,
                    'active' => true,
                ]
            );
        }

        $toiletryItems = [
            [
                'name' => 'Brush/comb',
                'short' => 'B/CB',
            ],
            [
                'name' => 'Conditioner',
                'short' => 'C',
            ],
            [
                'name' => 'Deodorant',
                'short' => 'D',
            ],
            [
                'name' => 'Sanitary Products',
                'short' => 'San',
            ],
            [
                'name' => 'Shampoo',
                'short' => 'SH',
            ],
            [
                'name' => 'Shower Gel',
                'short' => 'SG',
            ],
            [
                'name' => 'Soap',
                'short' => 'S',
            ],
            [
                'name' => 'Toothbrush',
                'short' => 'TB',
            ],
            [
                'name' => 'Toothpaste',
                'short' => 'TP',
            ],
            [
                'name' => 'Wipes',
                'short' => 'W',
            ],
        ];

        foreach ($toiletryItems as $item) {
            ServiceItem::updateOrCreate(
                [
                    'short' => $item['short'],
                ],
                [
                    ...$item,
                    'service_category_id' => $toiletries->id,
                    'active' => true,
                ]
            );
        }
    }
}
