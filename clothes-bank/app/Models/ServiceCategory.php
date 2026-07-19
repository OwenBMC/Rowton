<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'management_type',
        'track_history',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function eligibilityRules()
    {
        return $this->hasMany(
            EligibilityRule::class
        );
    }

    public function items()
    {
        return $this->hasMany(ServiceItem::class);
    }
}
