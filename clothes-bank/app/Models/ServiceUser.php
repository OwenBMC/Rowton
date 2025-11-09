<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceProvided;

class ServiceUser extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'nickname', 'housing_status'];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function servicesProvided()
    {
        return $this->hasMany(ServiceProvided::class);
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

