<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
        'id',
        'name',
        'established_year',
        'address',
        'description',
        'web_link',
        'other_software',
        'other',
        'postcode',
        'practice_role_id',
        'other_use_software',
        'phone',
        'profile',
        'recruiter_id',
        'practice_size',
        'primarly_looking',
        'working_hours',
        'other_dentistry',
        'other_practice_role',
        'document_name',
        'document',
        'verification',
        'status',
    ];

    protected $casts = [
    'branch_id' => 'array',
];

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class);
    }

    public function review()
    {
        return $this->hasMany(ClinicReview::class);
    }

    public function job()
    {
        return $this->hasMany(JobListing::class);
    }

    public function practiceRole()
    {
        return $this->belongsTo(PracticeRole::class, 'practice_role_id');
    }

    public function interview()
    {
        return $this->hasMany(ScheduleInterview::class, 'clinic_id');
    }

    public function scopeSearch($query, $search)
    {
        if (!empty($search->search)) {
            $query->where('name', 'like', '%' . $search->search . '%');
        }

        if (isset($search->status) && $search->status != '') {
            $query->where('status', $search->status);
        }

        if (!empty($search->date)) {
            $date = explode('-', $search->date);

            $query->whereBetween('created_at', [
                date('Y-m-d', strtotime($date[0])),
                date('Y-m-d', strtotime($date[1])),
            ]);
        }
    }
}