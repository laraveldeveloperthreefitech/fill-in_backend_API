<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateFCMToken extends Model
{
     protected $fillable = ['id', 'candidate_id','device_id','fcm_token', 'created_at', 'updated_at'];
}
