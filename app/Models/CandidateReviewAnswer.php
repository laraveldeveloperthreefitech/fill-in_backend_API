<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateReviewAnswer extends Model
{
    protected $fillable = [
        'candidate_review_id',
        'candidate_feedback_question_id',
        'answer',
    ];

    public function review()
    {
        return $this->belongsTo(CandidateReview::class, 'candidate_review_id');
    }

    public function question()
    {
        return $this->belongsTo(CandidateFeedbackQuestion::class, 'candidate_feedback_question_id');
    }
    
}