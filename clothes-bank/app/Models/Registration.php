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
    ];
}
