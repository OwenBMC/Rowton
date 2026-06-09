<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'service_user_id',
        'attendance_date',
        'arrival_time',
        'departure_time',
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }
}
