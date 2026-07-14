<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBranch extends Model
{
    protected $fillable = [
        'user_id',
        'branch',
    ];

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class, 'user_id');
    }
}