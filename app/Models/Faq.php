<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'status',
        'role',
    ];

    public function scopeSearch($query, $search)
    {
        if (!empty($search->search)) {
            $query->where('question', 'like', '%' . $search->search . '%');
        }
    
        if (!empty($search->id) && $search->module == 'clinic') {
            $query->where('clinic_id', $search->id);
        }
    
        if (!empty($search->id) && $search->module == 'candidate') {
            $query->whereHas('candidates', function ($q) use ($search) {
                $q->where('candidates.id', $search->id);
            });
        }
    
        if (isset($search->status) && $search->status != '') {
            $query->where('status', $search->status);
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
