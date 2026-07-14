<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruiterRecentSearch extends Model
{
     protected $fillable = ['id', 'recruiter_id', 'term', 'created_at', 'updated_at'];
}
