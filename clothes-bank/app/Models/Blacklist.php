<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected $table = 'blacklist';

    protected $fillable = [
        'service_user_id',
        'note',
        'blacklist_start_date',
        'blacklist_end_date',
    ];

    protected $casts = [
        'blacklist_start_date' => 'datetime',
        'blacklist_end_date' => 'datetime',
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class);
    }
}
