<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /*
        |--------------------------------------------------------------------------
        | INTERVIEW TYPE
        |--------------------------------------------------------------------------
        */

        $interviewDateTime = $this->date . ' ' . $this->time;
        $interviewEndTime  = $this->date . ' ' . $this->end_time;
        $currentDateTime   = date('Y-m-d H:i');

        if ($this->status == 1) {
            $type = 'Completed';
        } elseif ($interviewDateTime > $currentDateTime && $this->date != date('Y-m-d')) {
            $type = 'Upcoming';
        } elseif ($interviewEndTime < $currentDateTime) {
            $type = 'Expired';
        } else {
            $type = 'Today';
        }

        /*
        |--------------------------------------------------------------------------
        | BASIC DATA
        |--------------------------------------------------------------------------
        */

        $data = [
            'id'               => $this->id,
            'candidate_id'     => $this->candidate_id,
            'job_id'           => $this->job_id,

            'title'            => $this->title,
            'job_name'         => $this->job ? $this->job->title : '',

            'date'             => $this->date,
            'time'             => $this->time,
            'end_time'         => $this->end_time,
            'link'             => $this->link,

            'type'             => $type,
            'interview_status' => $this->interview_status,
        ];

        /*
        |--------------------------------------------------------------------------
        | RECRUITER LOGIN DATA
        |--------------------------------------------------------------------------
        */

        if (auth()->guard('recruiter')->check()) {

            $data['candidate'] = $this->candidate
                ? $this->candidate->name
                : '';

            $data['profile'] = ($this->candidate && $this->candidate->profile)
                ? config('filepaths.candidate.public_url') . $this->candidate->profile
                : '';

            $data['candidate_profession'] = (
                $this->candidate &&
                $this->candidate->specialization
            )
                ? $this->candidate->specialization->name
                : '';
        }

        /*
        |--------------------------------------------------------------------------
        | CANDIDATE LOGIN DATA
        |--------------------------------------------------------------------------
        */

        else if (auth()->guard('candidate')->check()) {

            $data['clinic'] = $this->clinic
                ? $this->clinic->name
                : '';

            $data['profile'] = ($this->clinic && $this->clinic->profile)
                ? config('filepaths.recruiter.public_url') . $this->clinic->profile
                : '';
        }

        return $data;
    }
}