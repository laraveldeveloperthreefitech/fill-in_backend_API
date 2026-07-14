<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportOnCandidate extends Model
{
    use HasFactory;

    // Explicitly define table name
    protected $table = 'report_on_candidates';

    protected $fillable = [
        'candidate_id',
        'clinic_id',
        'title',
        'description',
        'image',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeSearch($query, $search)
    {
        if (!empty($search->search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search->search . '%')
                  ->orWhere('description', 'like', '%' . $search->search . '%');
            });
        }
    
        if (!empty($search->date)) {
            $date = explode('-', $search->date);
            $query->whereBetween('created_at', [
                date('Y-m-d', strtotime($date[0])),
                date('Y-m-d', strtotime($date[1]))
            ]);
        }
    }
    
}
