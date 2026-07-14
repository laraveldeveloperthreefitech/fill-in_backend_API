<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportOnRecruiter extends Model
{
    protected $fillable = ['id', 'candidate_id', 'recruiter_id', 'title', 'description', 'image', 'created_at', 'updated_at'];


    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function recruiter()
    {
        return $this->belongsTo(JobListing::class, 'recruiter_id');
    }

    public function scopeSearch($query, $search)
    {
        if (!empty($search->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search->search . '%')
                  ->orWhere('description', 'like', '%' . $search->search . '%');
            });
        }
    
        if (!empty($search->date)) {
            $date = explode('-', $search->date);
            $query->whereBetween('created_at', [
                date('Y-m-d', strtotime($date[0])),
                date('Y-m-d', strtotime($date[1]))
            ]);
        }
    }
}
