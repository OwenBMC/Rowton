<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
protected $fillable = [
    'user_id',
    'first_name',
    'last_name',
    'phone',
];

public function user()
{
    return $this->belongsTo(User::class);
}
}
