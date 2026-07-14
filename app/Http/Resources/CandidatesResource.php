<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidatesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        
        
        $data = [
            'id'                        =>  $this->id,
            'name'                      =>  $this->name,
            'email'                     =>  $this->email,
            'phone'                     =>  $this->phone,
            'location'                  =>  $this->address,
            'profile'                   =>  $this->profile ? config('filepaths.candidate.public_url') . $this->profile : '',
            'type_of_experiance'        =>  $this->type_of_experiance,
            'other_qualification'       =>  $this->other_qualification,
            'other_software'            =>  $this->other_software,
            'other_vaccination'         =>  $this->other_vaccination,
            'document'                  =>  $this->document ? config('filepaths.candidate.document_url') . $this->document : '',
            'year_of_experiance'        =>  $this->year_of_experiance,
            'hourly_rate'               =>  $this->hourly_rate,
            'radius'                    =>  $this->radius,
            'availability_time'         =>  json_encode($this->availability_time),
            'short_notice'              =>  $this->short_notice,
            'permanent_opportunities'   =>  $this->permanent_opportunities,
            'childrens_check'           =>  $this->childrens_check,
            'valid_police_check'        =>  $this->valid_police_check,
            'first_aid_certicate'       =>  $this->first_aid_certicate,
            'working_in_dentistry'      =>  $this->working_in_dentistry,
            'environment_thrive'        =>  json_decode($this->environment_thrive),
            'fun_fact'                  =>  $this->fun_fact,
            'language'                  =>  $this->languages ? $this->languages->pluck('name') : '',
            'profession'                =>  $this->specialization_name, 
            'software_experiance'       =>  $this->software_experiance ? $this->software_experiance->pluck('name') : '', 
            'qualification'             =>  $this->qualification ? $this->qualification->pluck('name') : '', 
            'vaccination'               =>  $this->Vaccination ? $this->Vaccination->pluck('name') : '', 
            'rating'                    => count($this->review) > 0 ? $this->review->sum('rate')/count($this->review) : 0,
            'reviews'                   => !empty($this->review) ? $this->review->map(function($item){
                                             return [
                                                 'id'            => $item->id,
                                                'recruiter_name'   => $item->clinic->recruiter_id,
                                                'clinic_name'   => $item->clinic->name,
                                                'recruiter_name'=> $item->clinic->recruiter ? $item->clinic->recruiter->name : '',
                                                 'Clinic_profile'=> $item->clinic ? ($item->clinic->profile ? config('filepaths.recruiter.public_url') . $item->clinic->profile : null) : null,
                                                'rate'          => $item->rate,
                                                'comment'       => $item->comment,
                                                'image'         => $item->image ? config('filepaths.review.public_url') . $item->image : null,
                                                'created'       => $item->created_at,
                                                ];
                                            }) : [],
            'review_count'              =>  count($this->review),
            'candidate_availibity'      => json_decode($this->candidate_availibity),
            'is_schedule'               =>  count($this->interview) > 0 ? ($this->interview->where('status',1)->first() ? 1 : 0) : 0,
           
           
            
        ];

        if(auth()->guard('recruiter')->check()){
        	$clinicID = auth()->guard('recruiter')->user()->clinic ? auth()->guard('recruiter')->user()->clinic->id : false;
            $count  =  $clinicID ?  $this->job->where('clinic_id', $clinicID)->count() : 0;
             $data['applied_our_Job']           =  $count;
        $data['is_schedule']               =  count($this->interview) > 0 ? ($this->interview->where('clinic_id',$clinicID)->where('status',1)->first() ? 1 : 0) : 0;
         	$data['is_review']                 =  !empty($this->review) && $clinicID ? ($this->review->where('clinic_id',$clinicID)->first() ? 1 : 0) : 0 ;
             $data['is_report']                =  $clinicID ? ($this->candidateReport->where('clinic_id', auth()->guard('recruiter')->user()->clinic->id)->first() ? 1 : 0) : 0;
        	$data['is_profile_complete']	   = auth()->guard('recruiter')->user()->clinic ? 1 : 0;
        }
        return $data;
    }
}
