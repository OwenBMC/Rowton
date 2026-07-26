<?php

namespace App\Models;

use App\Traits\RecordsActor;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use RecordsActor;
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
