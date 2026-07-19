<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'management_type',
        'active',
        'track_history',
        'default_frequency_days',
    ];

    protected $casts = [
        'active' => 'boolean',
        'track_history' => 'boolean',
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

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }
}
