<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'recruiter_id',
        'name'
    ];

    public function recruiter()
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    // public function jobs()
    // {
    //     return $this->belongsToMany(Job::class, 'branch_job');
    // }

    public function jobs()
{
    return $this->belongsToMany(
        JobListing::class,
        'branch_job',
        'branch_id',
        'job_id'
    );
}

public function candidate()
{
    return $this->belongsTo(Candidate::class, 'candidate_id');
}
}