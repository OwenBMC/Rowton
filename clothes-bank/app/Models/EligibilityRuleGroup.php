<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibilityRuleGroup extends Model
{
    protected $fillable = [
        'eligibility_rule_id',
        'operator',
    ];

    public function rule()
    {
        return $this->belongsTo(
            EligibilityRule::class,
            'eligibility_rule_id'
        );
    }

    public function conditions()
    {
        return $this->hasMany(
            EligibilityCondition::class
        );
    }
}
