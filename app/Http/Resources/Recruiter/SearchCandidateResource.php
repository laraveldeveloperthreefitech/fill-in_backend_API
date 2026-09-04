<?php

namespace App\Http\Resources\Recruiter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchCandidateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'phone' => $this->phone,

            'address' => $this->address,

            'profile' => $this->profile
                ? config('filepaths.candidate.public_url') . $this->profile
                : null,

            'specialization_id' => optional($this->specialization)->id,

            'specialization' => optional($this->specialization)->name,

            'year_of_experiance' => $this->year_of_experiance ?? '0-1 Years',

            'hourly_rate' => $this->hourly_rate,

            'rating' => round($this->review?->avg('rate') ?? 0, 1),

            'review_count' => $this->review?->count() ?? 0,

            // 'shift_date' => $request->shift_date,

            // 'start_time' => date('H:i:s', strtotime($request->start_time)),

            // 'end_time' => date('H:i:s', strtotime($request->end_time)),
        ];
    }
}