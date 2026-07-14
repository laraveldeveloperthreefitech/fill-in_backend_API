<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleInterview extends Model
{
    protected $fillable = ['id','title','interview_status','candidate_id','job_id','end_time','clinic_id', 'date', 'time', 'link','status','notes', 'created_at', 'updated_at','timezone'];

    public function candidate(){
        return $this->belongsTo(Candidate::class,'candidate_id');
    }

    public function job(){
        return $this->belongsTo(JobListing::class,'job_id');
    }

    public function clinic(){
        return $this->belongsTo(Clinic::class,'clinic_id')->with('recruiter');
    }
    
}
