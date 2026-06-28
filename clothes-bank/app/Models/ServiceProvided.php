<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvided extends Model
{
    protected $table = 'services_provided';

    protected $fillable = [
        'service_category',
        'service_name',
        'attendance_date',
        'service_user_id',
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }
}
