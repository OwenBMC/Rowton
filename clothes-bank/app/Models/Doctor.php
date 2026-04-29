<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'practice_id',
        'phone_number',
        'email',
    ];

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function serviceUsers()
    {
        return $this->belongsToMany(ServiceUser::class, 'doctor_service_user');
    }
}
