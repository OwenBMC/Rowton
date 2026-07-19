<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'short',
        'active',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function policy()
    {
        return $this->hasOne(ServicePolicy::class);
    }

    public function eligibilityRules()
    {
        return $this->hasMany(
            EligibilityRule::class
        );
    }

    public function rules()
    {
        return $this->hasMany(ServiceRule::class);
    }
}
