<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateSupport extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'message',
        'response_status'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
