<?php

namespace App\Http\Resources\Candidate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Software;

class ShiftDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $clinic = $this->clinic;
        $recruiter = optional($clinic)->recruiter;

        $softwareNames = [];

        if (!empty($this->software)) {

            $softwareNames = Software::whereIn('id', $this->software)
                ->pluck('name')
                ->values();
        }

        return [

            'id' => $this->id,

            'title' => $this->title,

            'clinic_id' => $clinic?->id,

            'recruiter_id' => $recruiter?->id,

            'practice_name' => $clinic?->name,

            'specialization_id' => $this->specialization_id,

            'specialization' => optional($this->specialization)->name,

            'experiance_level' => $this->experiance_level,

            'software' => $this->software,

            'software_names' => $softwareNames,

            'other_software' => $this->other_software,

            'vacancy' => $this->vacancy,

            'urgent' => $this->urgent,

            'hourly_rate' => (string) $this->hourly_rate,

            'description' => $this->job_description,

            'benefits' => $this->benefits,

            'expire_date' => $this->expire_date,

            'address' => $this->address,

            'short_address' => $this->short_address,

            'latitude' => $this->latitude,

            'longitude' => $this->longitude,

            'practice_profile' => $recruiter && $recruiter->profile
                ? config('filepaths.recruiter.public_url').$recruiter->profile
                : null,

            'shift_date' => optional($this->shift_date)->format('Y-m-d'),

            'start_time' => date('H:i', strtotime($this->start_time)),

            'end_time' => date('H:i', strtotime($this->end_time)),

            'candidate_id' => $this->candidate_id,

            'response' => $this->response,

            'responded_at' => $this->responded_at,

            'is_my_booking' => $this->is_my_booking,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}