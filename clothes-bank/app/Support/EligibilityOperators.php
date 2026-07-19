<?php

namespace App\Support;

class EligibilityOperators
{
    public static function for(string $type): array
    {
        return match ($type) {

            'enum' => [
                '=',
                '!=',
            ],

            'number' => [
                '=',
                '>',
                '<',
                '>=',
                '<=',
            ],

            'date' => [
                'before',
                'after',
            ],

            'boolean' => [
                '=',
            ],

            default => [
                '=',
            ],
        };
    }
}
