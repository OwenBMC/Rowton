<?php

namespace Database\Seeders;

use App\Models\Terminology;
use Illuminate\Database\Seeder;

class TerminologySeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
            [
                'key' => 'service_user',
                'label' => 'Service User',
                'value' => 'Guest',
            ],
            [
                'key' => 'blacklisted',
                'label' => 'Blacklisted',
                'value' => 'Barred',
            ],
            [
                'key' => 'attendance',
                'label' => 'Attendance',
                'value' => 'Attendance',
            ],
            [
                'key' => 'attendee',
                'label' => 'Attendee',
                'value' => 'Guest',
            ],
        ];

        foreach ($terms as $term) {
            Terminology::updateOrCreate(
                ['key' => $term['key']],
                $term
            );
        }
    }
}
