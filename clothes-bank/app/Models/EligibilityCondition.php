<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibilityCondition extends Model
{
    protected $fillable = [
        'eligibility_rule_group_id',
        'attribute',
        'operator',
        'value',
    ];

    public function group()
    {
        return $this->belongsTo(
            EligibilityRuleGroup::class,
            'eligibility_rule_group_id'
        );
    }
}
