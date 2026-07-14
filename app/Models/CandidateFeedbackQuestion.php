<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateFeedbackQuestion extends Model
{
    protected $fillable = [
        'question',
        'type',
        'is_required',
        'status',
        'sort_order',
    ];

    public function answers()
    {
        return $this->hasMany(CandidateReviewAnswer::class, 'candidate_feedback_question_id');
    }
}