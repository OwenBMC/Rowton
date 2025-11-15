<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
        protected $table = 'attendance';

        protected $fillable = [
            'service_user_id',
            'attendance_date',
            'arrival_time',
            'departure_time',
        ];
}
