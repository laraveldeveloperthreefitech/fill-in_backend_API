<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $fillable = ['id', 'education_type', 'candidate_id', 'course', 'course_type', 'specialization', 'university', 'start_year', 'end_year', 'grade', 'created_at', 'updated_at'];
}
