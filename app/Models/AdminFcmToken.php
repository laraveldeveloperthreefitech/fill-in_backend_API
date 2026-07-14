<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminFcmToken extends Model
{
    protected $fillable = ['id', 'session_id', 'user_id', 'fcm_token', 'created_at', 'updated_at'];
}
