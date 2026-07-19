<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvided extends Model
{
    protected $table = 'services_provided';

    protected $fillable = [
        'service_user_id',
        'service_item_id',
        'attendance_date',
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}
