<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_names',
        'surname',
        'nickname',
        'housing_status',
        'contact_number',
        'address',
        'postcode',
        'food_allergies',
        'dob',
        'gender',
    ];

    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        $fullName = trim("{$this->first_name} {$this->middle_names} {$this->surname}");

        return $this->nickname
            ? "{$fullName} ({$this->nickname})"
            : $fullName;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function servicesProvided()
    {
        return $this->hasMany(ServiceProvided::class);
    }

    public function nextOfKin()
    {
        return $this->hasOne(NextOfKin::class);
    }

    public function practices()
    {
        return $this->belongsToMany(Practice::class, 'practice_service_user');
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_service_user');
    }

    public function registration()
    {
        return $this->hasOne(Registration::class);
    }

    public function blacklist()
    {
        return $this->hasMany(BlackListed::class)
            ->whereDate('blacklist_start_date', '<=', now())
            ->whereDate('blacklist_end_date', '>=', now());
    }

    public function getIsBlacklistedAttribute()
    {
        return $this->blacklist()->exists();
    }
}
