<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruiterFCMToken extends Model
{
    protected $fillable = ['id', 'recruiter_id','device_id','fcm_token', 'created_at', 'updated_at'];
}
