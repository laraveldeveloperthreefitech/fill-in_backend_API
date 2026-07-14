<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateSearch extends Model
{
    protected $fillable = ['id', 'term', 'count', 'created_at', 'updated_at'];
}
