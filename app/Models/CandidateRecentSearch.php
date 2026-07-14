<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateRecentSearch extends Model
{
    protected $fillable = ['id', 'candidate_id', 'term', 'created_at', 'updated_at'];
}
