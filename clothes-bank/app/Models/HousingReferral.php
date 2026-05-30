<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HousingReferral extends Model
{
    protected $fillable = [
        'service_user_id',
        'gender',
        'date_of_birth',
        'contact_number',
        'national_insurance_number',
        'nationality',
        'previous_address',
        'prison',
        'hospital',
        'fda',
        'housing_points',
        'medical_conditions',
        'first_contact',
        'second_contact',
        'third_contact',
        'notes',
        'outcome',
        'sleeping_bag',
        'referral_date',
    ];

    protected $casts = [
        'prison' => 'boolean',
        'hospital' => 'boolean',
        'sleeping_bag' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }
}
