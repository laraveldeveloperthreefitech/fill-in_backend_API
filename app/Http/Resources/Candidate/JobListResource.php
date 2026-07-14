<?php

namespace App\Http\Resources\candidate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       
        $data =  [
            'id'                => $this->id,
            'title'             => $this->title,
            'recruiter_id'      => $this->clinic->recruiter ? $this->clinic->recruiter->id : '',
            'practice_size'		=> $this->clinic->practice_size,
            'clinic'            => $this->clinic ? $this->clinic->name : '',
            'clinic_description'=> $this->clinic->description,
            'web_link'          => $this->clinic->web_link,
            'salary_range_from' => $this->salary_range_from,
            'salary_range_to'   => $this->salary_range_to,
            'experiance_level'  => $this->experiance_level,
            'clinic_logo'		=> $this->clinic && $this->clinic->profile  ? config('filepaths.recruiter.public_url') . $this->clinic->profile : '',
            'shift'             => $this->shift ? explode(',',$this->shift) : [],
            'software'          => !empty($this->softwareList) ? $this->softwareList->pluck('name') : [],
            'profession'        => $this->specialization ? $this->specialization->name : '',
        	// 'employmentTypes'   => $this->employmentTypes->map(function ($item) {
            //                             return [
            //                                 'value' => $item->id,
            //                                 'key' => $item->name,
            //                             ];
            //                         }),
            'vacancy'			=> $this->vacancy,
            // 'require_document'  => !empty($this->requireDocuments) ? $this->requireDocuments->map(function ($item) {
            //                             return [
            //                                 'value' => $item->id,
            //                                 'key' => $item->name,
            //                             ];
            //                         }) : [],
            // 'profession'        => !empty($this->specialization) ? $this->specialization->map(function ($item) {
            //                             return [
            //                                 'value' => $item->id,
            //                                 'key' => $item->name,
            //                             ];
            //                         }) : [],
         	'city'              => $this->city,
            'job_description'   => $this->job_description,
            'address'           => $this->address,
            'benefits'          => explode(',',$this->benefits),
            'expire_date'       => $this->expire_date ? date('Y-m-d',strtotime($this->expire_date)) : '',
            'require_document'  => $this->require_document,
            'candidates_count'  => $this->candidates_count ?? 0,
            'created_at'        => $this->created_at,
            'latitude'          =>  $this->latitude,
            'longitude'         =>  $this->longitude,
            'time' 				=> $this->created_at->diffForHumans(),
        	'urgent'            => $this->urgent,
                  
            
        ];

        if (auth()->guard('candidate')->check()) {
            $candidate = $this->candidates->where('id', auth()->guard('candidate')->user()->id)->first();
            $data['is_report']       = $this->jobReport->where('candidate_id', auth()->guard('candidate')->user()->id)->first() ? 1 : 0;
            $data['is_saved']       = $this->bookmarked->where('id', auth()->guard('candidate')->user()->id)->first() ? 1 : 0;
            $data['applied']        = $candidate ? 1 : 0;
            $data['applied_track']  = $candidate ? explode(',', $candidate->pivot->status) : [];
        }

        return $data;
    }
}
