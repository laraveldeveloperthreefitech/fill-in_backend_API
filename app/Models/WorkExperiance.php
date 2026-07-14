<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperiance extends Model
{
    protected $fillable = ['id', 'is_current', 'candidate_id', 'employment_type_id', 'company_name', 'job_title', 'from_date', 'to_date', 'job_profile', 'total_experiance', 'joining_date', 'current_annual_salary', 'created_at', 'updated_at'];

    public function employment(){
        return $this->belongsTo(EmploymentType::class,'employment_type_id');
    }
}
