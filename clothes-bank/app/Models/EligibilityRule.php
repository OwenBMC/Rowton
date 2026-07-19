<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibilityRule extends Model
{
    protected $fillable = [
        'service_item_id',
        'service_category_id',
        'name',
        'active',
    ];

    public function groups()
    {
        return $this->hasMany(
            EligibilityRuleGroup::class
        );
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function category()
    {
        return $this->belongsTo(
            ServiceCategory::class
        );
    }

    public function conditionGroups()
    {
        return $this->hasMany(
            EligibilityRuleGroup::class
        );
    }
}
