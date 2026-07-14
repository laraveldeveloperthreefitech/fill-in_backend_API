<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicFeedbackQuestion extends Model
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
        return $this->hasMany(ClinicReviewAnswer::class, 'clinic_feedback_question_id');
    }
}