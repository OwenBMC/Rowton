<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePolicy extends Model
{
    protected $fillable = [
        'service_item_id',
        'cooldown_days',
        'manager_override',
        'enabled',
    ];

    public function item()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}
