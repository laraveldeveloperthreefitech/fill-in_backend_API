<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['id', 'canditidate_id', 'recruiter_id', 'sendBy', 'message','status','created_at', 'updated_at'];

    public function candidates(){
        return $this->belongsTo(Candidate::class,'canditidate_id');
    }

    public function recruiters(){
        return $this->belongsTo(Recruiter::class,'recruiter_id');
    }
}
