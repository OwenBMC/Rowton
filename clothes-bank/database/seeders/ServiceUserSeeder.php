<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('service_users')->insert([
            [
                'first_name' => null,
                'middle_names' => null,
                'surname' => null,
                'nickname' => 'Unknown',
                'housing_status' => 'Unknown',
            ],
            [
                'first_name' => 'Bobby',
                'middle_names' => null,
                'surname' => 'Jones',
                'nickname' => 'Bobbert',
                'housing_status' => 'Rough Sleeper',
            ],
            [
                'first_name' => 'Robert',
                'middle_names' => null,
                'surname' => 'Web',
                'nickname' => null,
                'housing_status' => 'Housed',
            ],
            [
                'first_name' => null,
                'middle_names' => null,
                'surname' => null,
                'nickname' => 'Mysterious Stranger',
                'housing_status' => 'Rough Sleeper',
            ],
            [
                'first_name' => 'Andrew',
                'middle_names' => null,
                'surname' => 'Garfield',
                'nickname' => 'Mondays',
                'housing_status' => 'Hostel',
            ],
            [
                'first_name' => 'Benjamin',
                'middle_names' => null,
                'surname' => 'Button',
                'nickname' => null,
                'housing_status' => 'Housed',
            ],
            [
                'first_name' => 'Caroline',
                'middle_names' => null,
                'surname' => 'Crumpet',
                'nickname' => 'Crumpy',
                'housing_status' => 'Unknown',
            ],
        ]);

    }
}
