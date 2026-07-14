<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FillinShiftCancellationRequest extends Model
{
    use HasFactory;

    protected $table = 'fillin_shift_cancellation_requests';

    protected $fillable = [
        'fillin_shift_id',
        'candidate_id',
        'clinic_id',
        'reason',
        'notes',
        'attachment',
        'status',
        'recruiter_remark',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function shift()
    {
        return $this->belongsTo(FillinShift::class, 'fillin_shift_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function recruiter()
    {
        return $this->belongsTo(Recruiter::class, 'approved_by');
    }
}