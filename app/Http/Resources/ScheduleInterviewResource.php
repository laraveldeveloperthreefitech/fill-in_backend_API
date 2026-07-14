<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleInterviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data =  [
                    'id'                        => $this->id,
                    'title'                     => $this->title,
                    'candidate_id'              => $this->candidate_id,
                    'job_id'                    => $this->job_id,
                    'job_name'                  => $this->job ? $this->job->title : '',
                    'date'                      => $this->date,
                    'time'                      => $this->time,
                    'end_time'                  => $this->end_time,
                    'location'                  => $this->job ? $this->job->address : '',
                    'job_description'           => $this->job ? $this->job->job_description : '',
                    'link'                      => $this->link,
                    'notes'                     => $this->notes,
                    'type'                      => $this->status == 1 ? 'Completed' : ($this->date . ' ' .$this->time > date('Y-m-d H:i') && $this->date != date('Y-m-d')  ? 'Upcoming' : ($this->date . ' ' .$this->end_time < date('Y-m-d H:i') ? 'Expired' : 'Today')),
                    'created_at'                => $this->created_at,
                    'interview_status'          => $this->interview_status,
                ];
                
                if (auth()->guard('recruiter')->check()) {
                    $data['candidate']            = $this->candidate ? $this->candidate->name : '';
                    $data['candidate_email']      = $this->candidate ? $this->candidate->email : '';
                    $data['candidate_profession'] = $this->candidate && $this->candidate->specialization
                                                    ? $this->candidate->specialization->name : '';
                    $data['Experience']           = $this->candidate ? $this->candidate->year_of_experiance : '';
                } elseif (auth()->guard('candidate')->check()) {
                    $data['clinic']        = $this->clinic ? $this->clinic->name : '';
                    $data['clinic_email']  = $this->clinic && $this->clinic->recruiter
                                            ? $this->clinic->recruiter->email : '';
                    $data['practice_role'] = $this->clinic && $this->clinic->practiceRole
                                            ? $this->clinic->practiceRole->name : '';
                }


        return $data;
    }
}
