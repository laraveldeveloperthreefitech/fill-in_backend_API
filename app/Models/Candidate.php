<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use App\Models\{RecruiterSearch,RecruiterRecentSearch};



class Candidate extends Authenticatable implements JWTSubject
{
    
    use Notifiable;
    protected $fillable = ['id', 'name', 'email', 'phone', 'profile', 'first_name', 'last_name','candidate_availibity','year_of_experiance', 'availability_time', 'type_of_experiance', 'other_qualification', 'other_software','other','before_image','after_image', 'other_vaccination', 'address', 'radius', 'document_name', 'hourly_rate', 'short_notice', 'permanent_opportunities', 'childrens_check', 'valid_police_check', 'first_aid_certicate', 'working_in_dentistry', 'environment_thrive', 'specialization_name', 'fun_fact', 'dob', 'gender', 'password', 'verified', 'phone_verified', 'document', 'otp', 'expire_otp', 'device_token', 'social-media-key', 'language', 'latitude', 'longitude', 'login_type', 'timezone', 'status', 'created_at', 'updated_at'];
    protected $hidden = ['password'];
     
    public function interview(){
        return $this->hasMany(ScheduleInterview::class,'candidate_id');
    }
    public function fcmTokens()
    {
        return $this->hasMany(CandidateFCMToken::class); // adjust table/model name
    }
    public function job() {
        return $this->belongsToMany(JobListing::class, 'candidate_job')->withPivot('status')->withTimestamps();
    }

    public function deprtment(){
        return $this->belongsTo(Department::class);
    }
    public function candidateReport(){
          return $this->hasMany(ReportOnCandidate::class);
    }
    public function specialization() {
        return $this->belongsTo(Specialization::class, 'specialization_name','name');
    }

    public function software_experiance() {
        return $this->belongsToMany(Software::class, 'candidate_software')->withTimestamps();
    }
    public function qualification() {
        return $this->belongsToMany(Qualification::class, 'candidate_qualification')->withTimestamps();
    }
    public function languages() {
        return $this->belongsToMany(Language::class, 'candidate_language')->withTimestamps();
    }
    public function Vaccination() {
        return $this->belongsToMany(Vaccination::class, 'candidate_vaccination')->withTimestamps();
    }


    public function review(){
        return $this->hasMany(CandidateReview::class);
    }

    public function bookmarked() {
        return $this->belongsToMany(JobListing::class, 'book_markeds')->withTimestamps();
    }
    
    public function scopeSearch($query ,$search){
        if($search->search){
            $query->where('name', 'like','%'.$search->search.'%');
        }
        if(isset($search->status) && $search->status != ''){
            $query->where('status',$search->status);
        } 
        if(isset($search->date) && $search->date){
            $date = explode('-',$search->date);
            $query->whereBetween('created_at', [date('Y-m-d',strtotime($date[0])), date('Y-m-d',strtotime($date[1]))]);
        } 
    }

    public function scopeApiSearch($query ,$search){
       
        if ($search->search) {
            $term        = trim($search->search);
            // Handle popular search
            $popular        = RecruiterSearch::firstOrNew(['term' => $term]);
            $popular->count = ($popular->exists ? $popular->count + 1 : 1);
            $popular->save();
            if(auth()->guard('recruiter')->check()){
                $recruiterId = auth()->guard('recruiter')->user()->id;
                // Handle recent search
                $recent = RecruiterRecentSearch::where('recruiter_id', $recruiterId)
                    ->where('term', $term)
                    ->first();

                if (!$recent) {
                    $recentCount = RecruiterRecentSearch::where('recruiter_id', $recruiterId)->count();

                    if ($recentCount >= 5) {
                        RecruiterRecentSearch::where('recruiter_id', $recruiterId)
                            ->orderBy('id')
                            ->limit(1)
                            ->delete();
                    }

                    RecruiterRecentSearch::firstOrCreate(
                        ['recruiter_id' => $recruiterId, 'term' => $term],
                        ['recruiter_id' => $recruiterId, 'term' => $term]
                    );
                }
            }
            $query->where(function($query) use($search){
                 $query->where('specialization_name', 'like', '%' . $search->search . '%')
                     ->orWhere('address', 'like', '%' . $search->search . '%')
                     ->orWhereHas('software_experiance', function ($softwareQuery) use ($search) {
                         $softwareQuery->where('name', 'like', '%' . $search->search . '%');
                     });
            })->orderByDesc('id');
         
        }else{
              $query->whereNotNull('specialization_name');

    		if (auth()->guard('recruiter')->check() && empty($search->profession)) {
        		$looking = auth()->guard('recruiter')->user()->lookingFor()->pluck('name')->toArray();

        		if (!empty($looking)) {
            		$query->whereIn('specialization_name', $looking);
        		}
    		}

   			$query->orderByDesc('id');
        
        }
        $hasLocation = !empty($search->latitude) && !empty($search->longitude);
        if ($hasLocation) {
            $latitude = $search->latitude;
            $longitude = $search->longitude;
            // Priority: request radius -> admin global default (settings.radius) -> 50 km.
            $globalRadius = optional(\App\Models\Setting::first())->radius;
            $radius = $search->radius
                ? $search->radius
                : (!empty($globalRadius) ? $globalRadius : 50);

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
            $query->whereHas('software_experiance',function($query) use($search){
                $query->whereIn('software.id',$search->software);
            });
        }

       if ($search->profession && is_array($search->profession)) {
    		$query->whereHas('specialization', function ($q) use ($search) {
        		$q->whereIn('specializations.id', $search->profession);
    		});
		}


        if($search->location && is_array($search->location)){
            $query->whereIn('address','like', '%' . $search->location . '%');
        }

       if ($search->type_of_experiance && is_array($search->type_of_experiance)) {
    $type = array_map('strtolower', $search->type_of_experiance);
       if(in_array('public',$type)){
       		$type[] = "both";
       }else if(in_array('private',$type)){
       		$type[] = "both";
       }
     


    $query->whereIn('type_of_experiance', $type);
}


        
        if($search->year_of_experiance){
            $query->whereIn('year_of_experiance',$search->year_of_experiance);
        }

        if($search->hourly_rate && is_array($search->hourly_rate)){
            $query->whereIn('hourly_rate',$search->hourly_rate);
        }
        if($search->permanent_opp){
            $query->where('permanent_opportunities',$search->permanent_opp);
        }

       if ($search->language && is_array($search->language)) {
            $query->whereHas('languages', function ($query) use ($search) {
                $query->whereIn('languages.id', $search->language);
            });
        }


        if (!empty($search->rating) && is_array($search->rating)) {
    			$minRating = min($search->rating); // take the lowest value in the array
   				 $query->withAvg('review', 'rate')
          		->having('review_avg_rate', '>=', $minRating);
		}
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function fillinResponses()
{
    return $this->hasMany(
        FillinShiftResponse::class,
        'candidate_id'
    );
}

protected $casts = [

    'before_image' => 'array',

    'after_image' => 'array',

];

public function branches()
{
    return $this->hasMany(Branch::class, 'candidate_id');
}

}
