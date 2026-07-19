<?php

namespace App\Support;

class EligibilityAttributes
{
    public static function all(): array
    {
        return [

            'housing_status' => [
                'label' => 'Housing Status',
                'type' => 'enum',
            ],

            'gender' => [
                'label' => 'Gender',
                'type' => 'enum',
            ],

            'dob' => [
                'label' => 'Date of Birth',
                'type' => 'date',
            ],

            'created_at' => [
                'label' => 'Registration Date',
                'type' => 'date',
            ],

            'registration.status' => [
                'label' => 'Registration Status',
                'type' => 'enum',
            ],

            'is_blacklisted' => [
                'label' => 'Blacklisted',
                'type' => 'boolean',
            ],

        ];
    }
}
