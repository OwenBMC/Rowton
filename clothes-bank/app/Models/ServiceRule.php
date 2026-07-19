<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRule extends Model
{
    protected $fillable = [
        'service_item_id',
        'attribute',
        'operator',
        'value',
    ];

    public function item()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}
