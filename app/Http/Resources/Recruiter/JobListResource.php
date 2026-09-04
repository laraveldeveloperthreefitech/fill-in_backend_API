<?php

namespace App\Http\Resources\Recruiter;

use Illuminate\Http\Request;
use App\Models\UserBranch;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
	protected $candidateId;
	public function __construct($resource, $candidateId = null)
    {
        parent::__construct($resource);
        $this->candidateId = $candidateId;
    }

    public function toArray(Request $request): array
    {
   
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'recruiter_id'      => $this->clinic->recruiter ? $this->clinic->recruiter->id : '',
            'clinic'            => $this->clinic ? $this->clinic->name : '',
            'clinic_logo'		=> $this->clinic && $this->clinic->profile  ? config('filepaths.recruiter.public_url') . $this->clinic->profile : '',
            'salary_range_from' => $this->salary_range_from,
            'salary_range_to'   => $this->salary_range_to,
            'experiance_level'  => $this->experiance_level,
            'shift'             => $this->shift ? explode(',',$this->shift) : [],
           	// 'software'          => !empty($this->softwareList) ? $this->softwareList->pluck('id') : [],
           	'software' => $this->softwareList
    ? $this->softwareList->map(function ($software) {
        return [
            'id'   => $software->id,
            'name' => $software->name,
        ];
    })->values()
    : [],
            'profession'        => $this->specialization_id,
            'branches' => $this->branches->map(function ($branch) {

    return [
        'id'   => $branch->id,
        'name' => $branch->name,
    ];

})->values(),
        
        'other_software' => $this->other_software,
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
            // 'specialization'    => !empty($this->specialization) ? $this->specialization->map(function ($item) {
            //                             return [
            //                                 'value' => $item->id,
            //                                 'key' => $item->name,
            //                             ];
            //                         }) : [],
        	 'urgent'            => $this->urgent,
            'city'              => $this->city,
            'address'           => $this->address,
            'short_address'     => $this->short_address,
            'job_description'   => $this->job_description,
            'benefits'          => explode(',',$this->benefits),
            'expire_date'       => $this->expire_date ? date('Y-m-d',strtotime($this->expire_date)) : '',
            'candidates_count'  => $this->candidates_count,
            'latitude'          =>  $this->latitude,
            'longitude'         =>  $this->longitude,
        	'schedule' => $this->interView
    						? ($this->interView->where('candidate_id', $this->candidateId)->first() ? 1 : 0)
    							: '',

            'created_at'        => $this->created_at,
        ];
    }
}
