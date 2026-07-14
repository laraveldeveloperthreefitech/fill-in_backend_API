<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicReview extends Model
{
    protected $fillable = [
        'recruiter_id',
        'candidate_id',
        'rate',
        'comment',
        'image',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class, 'recruiter_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'recruiter_id');
    }

    public function answers()
    {
        return $this->hasMany(ClinicReviewAnswer::class, 'clinic_review_id');
    }

    public function scopeSearch($query, $request)
    {
        if (!empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%$search%")
                    ->orWhereHas('candidate', function ($q1) use ($search) {
                        $q1->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('recruiter', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%$search%");
                    });
            });
        }

        return $query;
    }
}