<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FillinShift extends Model
{
    protected $fillable = [
        'clinic_id',
        'title',
        'specialization_id',
        'experiance_level',
        'shift',
        'software',
        'other_software',
        'vacancy',
        'urgent',
        'city',
        'address',
        'short_address',
        'job_description',
        'benefits',
        'expire_date',
        'latitude',
        'longitude',
        'hourly_rate',
        'status',
        'booked_candidate_id',

    'branch_ids',          // ADD

    'shift_date',
    'start_time',
    'end_time',
    
    'candidate_completed',
    'candidate_completed_at',
    'completed_at'
    ];

    protected $casts = [
        'software'    => 'array',
        'expire_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'shift_date'  => 'date',
        //  'shift_date'  => 'string',
    'branch_ids' => 'array',
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    public function bookedCandidate()
    {
        return $this->belongsTo(Candidate::class, 'booked_candidate_id');
    }

    public function responses()
    {
        return $this->hasMany(FillinShiftResponse::class, 'fillin_shift_id');
    }

    public function cancellationRequests()
{
    return $this->hasMany(
        FillinShiftCancellationRequest::class,
        'fillin_shift_id'
    );
}

}