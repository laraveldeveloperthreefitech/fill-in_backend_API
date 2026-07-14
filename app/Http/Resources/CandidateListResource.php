<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected $jobId = null;

    public function withJobId($jobId)
    {
        $this->jobId = $jobId;
        return $this;
    }
    public function toArray(Request $request): array
    {
        $data =  [
                    'id'                    =>  $this->id,
                    'name'                  =>  $this->name,
                    'location'              =>  $this->address,
                    'profession'			=>  $this->specialization ? $this->specialization->name : '',
                    'hourly_rate'           =>  $this->hourly_rate,
                    'profile'               =>  $this->profile ? config('filepaths.candidate.public_url') . $this->profile : null,
                    'year_of_experiance'    =>  $this->year_of_experiance,
                    'rating'                =>  count($this->review) > 0 ? $this->review->sum('rate')/count($this->review) : 0,
                    'review_count'          =>  count($this->review)
                ];
         if ($this->jobId) {
            $job = $this->job->where('id', $this->jobId)->first();
            if ($job && $job->pivot) {
                $data['applied_date'] = $job->pivot->created_at;
                $data['applied_track'] =explode(',', $job->pivot->status);
            }
        }

        return $data;
        
    }
}
