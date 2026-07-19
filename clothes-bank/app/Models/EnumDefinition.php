<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnumDefinition extends Model
{
    protected $fillable = [
        'group',
        'key',
        'label',
        'active',
    ];

    public function values()
    {
        return $this->hasMany(
            EnumDefinition::class,
            'group',
            'group'
        );
    }
}
