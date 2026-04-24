<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextOfKin extends Model
{
    protected $table = 'next_of_kins';

    protected $fillable = [
        'service_user_id',
        'name',
        'relationship',
        'address',
        'contact_number',
    ];

    public function service_user()
    {
        return $this->belongsTo(ServiceUser::class);
    }
}
