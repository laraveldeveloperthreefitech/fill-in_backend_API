<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'about_us',
        'privacy_policy',
        'email',
        'phone',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'logo',
        'terms_conditions',
        'radius'
    ];
}
