<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
 use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 

class Recruiter extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $hidden = ['password'];

    // protected $fillable = ['id', 'name', 'email', 'phone', 'password','timezone','phone_verified', 'verified', 'otp', 'expire_otp', 'device_token', 'device_id', 'social-media-key', 'login_type', 'status', 'created_at', 'updated_at','profile'];
    
    protected $fillable = [
    'id', 'name', 'email', 'phone', 'password', 'timezone', 'phone_verified',
    'verified', 'otp', 'expire_otp', 'device_token', 'device_id',
    'social-media-key', 'login_type', 'status', 'created_at', 'updated_at',
    'profile', 'practice_role_id'  // <-- ADD THIS
];

    public function clinic(){
        return $this->hasOne(Clinic::class);
    }
    public function jobs(){
        return $this->hasOne(Clinic::class);
    }

    public function dentistryPractices() {
        return $this->belongsToMany(Specialization::class, 'recruiter_specialization');
    }

    public function lookingFor() {
        return $this->belongsToMany(Specialization::class, 'looking_for');
    }

    // public function RoleInPractice() {
    //     return $this->belongsToMany(PracticeRole::class, 'practice_role_recruiter');
    // }
    public function RoleInPractice() {
        return $this->belongsTo(PracticeRole::class, 'practice_role_id');
    }

    public function useSoftware() {
        return $this->belongsToMany(Software::class, 'recruiter_software');
    }

    public function review(){
        return $this->hasMany(ClinicReview::class);
    }

    // public function scopeSearch($query ,$search){
    //     if($search->search){
    //         $query->where('name', 'like','%'.$search->search.'%');
    //     }
    //     if(isset($search->status) && $search->status != ''){
    //         $query->where('status',$search->status);
    //     } 
    //     if(isset($search->date) && $search->date){
    //         $date = explode('-',$search->date);
    //         // dd($date);
    //         $query->whereBetween('created_at', [date('Y-m-d',strtotime($date[0])), date('Y-m-d',strtotime($date[1]))]);
    //     } 
    // }
    
    public function scopeSearch($query, $filters)
{
    // 🔍 Search by name
    if (!empty($filters->search)) {
        $query->where('name', 'like', '%' . $filters->search . '%');
    }

    // 📌 Status filter
    if ($filters->status !== null && $filters->status !== '') {
        $query->where('status', $filters->status);
    }

    // 📅 Date range filter
    if (!empty($filters->date)) {

        // Expected format: "MM/DD/YYYY - MM/DD/YYYY"
        $dates = explode(' - ', $filters->date);

        if (count($dates) === 2) {

            try {
                $startDate = Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                $endDate   = Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('created_at', [$startDate, $endDate]);

            } catch (\Exception $e) {
                \Log::error('Date filter error: ' . $e->getMessage());
            }
        }
    }

    return $query;
}

     public function fcmTokens()
    {
        return $this->hasMany(RecruiterFCMToken::class); // adjust table/model name
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
    
    
    // App\Models\Recruiter.php

public function branches()
{
    return $this->hasMany(Branch::class);
}

    


}
