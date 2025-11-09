<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void

    {

        DB::table('service_users')->insert([
            [
                'name' => null,
                'nickname' => 'Unknown',
                'housing_status' => 'Unknown',
            ],
            [
                'name' => 'Bobby Jones',
                'nickname' => 'Bobbert',
                'housing_status' => 'Rough Sleeper',
            ],
            [
                'name' => 'Robert Web',
                'nickname' => null,
                'housing_status' => 'Housed',
            ],
            [
                'name' => null,
                'nickname' => 'Mysterious Stranger',
                'housing_status' => 'Rough Sleeper',
            ],
            [
                'name' => 'Andrew Garfield',
                'nickname' => 'Mondays',
                'housing_status' => 'Hostel',
            ],
            [
                'name' => 'Benjamin Button',
                'nickname' => null,
                'housing_status' => 'Housed',
            ],
            [
                'name' => 'Caroline Crumpet',
                'nickname' => 'Crumpy',
                'housing_status' => 'Unknown',
            ]

        ]);

    }
}
