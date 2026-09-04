<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{CandidateSearch,CandidateRecentSearch};

class JobListing extends Model
{
    protected $fillable = ['id', 'title', 'clinic_id','city','other_software','salary_range_from', 'specialization_id', 'urgent','vacancy', 'shift', 'salary_range_to', 'experiance_level', 'job_description', 'require_detail', 'benefits', 'expire_date', 'latitude', 'longitude', 'require_document', 'address', 'short_address', 'status', 'created_at', 'updated_at'];
    
    public function clinic(){
        return $this->belongsTo(Clinic::class);
    }

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function jobReport(){
          return $this->hasMany(ReportOnJob::class, 'job_id');
    }
    public function softwareList() {
        return $this->belongsToMany(Software::class, 'job_listing_software');
    }

    public function specialization() {
        return $this->belongsTo(Specialization::class,);
    }

    public function candidates() {
        return $this->belongsToMany(Candidate::class, 'candidate_job')->withPivot('status')->withTimestamps();
    }
    
    public function employmentTypes()
    {
        return $this->belongsToMany(EmploymentType::class, 'employment_type_job_listing');
    }

    public function requireDocuments()
    {
        return $this->belongsToMany(RequireDocument::class, 'require_document_job_listing');
    }

    public function bookmarked() {
        return $this->belongsToMany(Candidate::class, 'book_markeds')->withTimestamps();
    }

	 public function interView(){
        return $this->hasMany(ScheduleInterview::class,'job_id');
    }

    public function scopeSearch($query ,$search){
        if($search->search){
            $query->where('title', 'like','%'.$search->search.'%');
        }
        if($search->id && $search->module == 'clinic'){
            $query->where('clinic_id',$search->id);
        }
        if($search->id && $search->module == 'candidate'){
            $query->whereHas('candidates', function ($query) use ($search) {
                $query->where('candidates.id', $search->id); // Explicit table name
            });
        }
        if(isset($search->status) && $search->status != ''){
            $query->where('status',$search->status);
        } 
        if(isset($search->date) && $search->date){
            $date = explode('-',$search->date);
            $query->whereBetween('created_at', [date('Y-m-d',strtotime($date[0])), date('Y-m-d',strtotime($date[1]))]);
        } 
    }

    public function scopeApiSearch($query, $search)
    {
        $hasSearch = !empty($search->search);
        $hasLocation = !empty($search->latitude) && !empty($search->longitude);

        if ($search->search) {
            $term        = trim($search->search);
            // Handle popular search
            $popular        = CandidateSearch::firstOrNew(['term' => $term]);
            $popular->count = ($popular->exists ? $popular->count + 1 : 1);
            $popular->save();
            if(auth()->guard('candidate')->check()){
                $candidateId = auth()->guard('candidate')->user()->id;
                // Handle recent search
                $recent = CandidateRecentSearch::where('candidate_id', $candidateId)
                    ->where('term', $term)
                    ->first();

                if (!$recent) {
                    $recentCount = CandidateRecentSearch::where('candidate_id', $candidateId)->count();

                    if ($recentCount >= 5) {
                        CandidateRecentSearch::where('candidate_id', $candidateId)
                            ->orderBy('id')
                            ->limit(1)
                            ->delete();
                    }

                    CandidateRecentSearch::create([
                        'candidate_id'  => $candidateId,
                        'term'          => $term
                    ]);
                }
            }
            // Apply search query
            $query->where(function($query) use($term){
                 $query->where('title', 'like', '%' . $term . '%')->orWhere('address', 'like', '%' . $term . '%')
                ->orWhere(function ($query) use ($term) {
                    $query->whereHas('specialization', function ($q) use ($term) {
                        $q->where('name', 'like', '%' . $term . '%');
                    });
                })->orWhere(function ($query) use ($term) {
                    $query->whereHas('softwareList', function ($q) use ($term) {
                        $q->where('name', 'like', '%' . $term . '%');
                    });
                });
            });
        }else{
             if(auth()->guard('candidate')->check() && auth()->guard('candidate')->user()->specialization_name){
                $query->where(function ($query) {
                    $query->whereHas('specialization', function ($q) {
                        $q->where('name', auth()->guard('candidate')->user()->specialization_name);
                    });
                });
            }
        }

        if ($hasLocation) {
            $latitude = $search->latitude;
            $longitude = $search->longitude;
            $radius = $search->radius ? $search->radius : 50; // 50 km

            $haversine = "(6371 * acos(cos(radians($latitude)) 
                            * cos(radians(latitude)) 
                            * cos(radians(longitude) - radians($longitude)) 
                            + sin(radians($latitude)) 
                            * sin(radians(latitude))))";

            $query->select('*')
                ->selectRaw("$haversine AS distance")
                ->having('distance', '<=', $radius)
                ->orderBy('distance', 'asc');
        }

        if($search->software && is_array($search->software)){
            $query->whereHas('softwareList', function ($q) use ($search) {
                $q->whereIn('software.id',$search->software);
            });
        }

        if($search->shift  && is_array($search->shift)){
             $query->where(function ($q) use ($search) {
                foreach ($search->shift as $term) {
                    $q->orWhere('shift', 'like', '%' . $term . '%');
                }
            });
        }

        if($search->profession && is_array($search->profession)){
            $query->whereHas('specialization', function ($q) use ($search) {
                 $q->whereIn('specializations.id',$search->profession);
            });
        }

        if($search->experiance_level){
            $query->whereIn('experiance_level',$search->experiance_level);
        }
    
    	$query->latest();

        // If no search term and no location, limit to 5 records
       
    }

    public function scopeLimit($query, $search){
        $hasSearch = !empty($search->search);
        $hasLocation = !empty($search->latitude) && !empty($search->longitude);
         if (!$hasSearch && !$hasLocation) {
            $query->take(10);
        }
     }




     public function branches()
{
    return $this->belongsToMany(
        Branch::class,
        'branch_job',
        'job_id',
        'branch_id'
    );
}


}
