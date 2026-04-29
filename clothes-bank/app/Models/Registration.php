<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'service_user_id',
        'dob',
        'address',
        'postcode',
        'contact_number',
        'food_allergies',
        'referral_date',
        'service_user_signature_date',
        'volunteer_signature_date',
        'next_of_kin_id',
        'doctor_id',
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }
}
