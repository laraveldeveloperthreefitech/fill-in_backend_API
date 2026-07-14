<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSupport extends Model
{
     protected $fillable = ['id', 'recruiter_id', 'message', 'response_status', 'created_at', 'updated_at'];

     public function clinic(){
        return $this->belongsTo(Recruiter::class);
    }
}
