<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicReviewAnswer extends Model
{
    protected $fillable = [
        'clinic_review_id',
        'clinic_feedback_question_id',
        'answer',
    ];

    public function review()
    {
        return $this->belongsTo(ClinicReview::class, 'clinic_review_id');
    }

    public function question()
    {
        return $this->belongsTo(ClinicFeedbackQuestion::class, 'clinic_feedback_question_id');
    }
}