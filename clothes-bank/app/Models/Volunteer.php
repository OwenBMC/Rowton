<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'pin',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}