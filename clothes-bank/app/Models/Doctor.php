<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'service_user_id',
        'name',
        'address',
        'contact_number',
    ];

    public function serviceUsers()
    {
        return $this->belongsToMany(ServiceUser::class, 'doctor_service_user');
    }
}
