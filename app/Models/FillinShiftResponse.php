<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FillinShiftResponse extends Model
{
    protected $fillable = [
        'fillin_shift_id',
        'candidate_id',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(FillinShift::class, 'fillin_shift_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}