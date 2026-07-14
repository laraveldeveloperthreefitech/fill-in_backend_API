<?php

namespace App\Http\Resources\Recruiter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Branch;

class SearchCandidateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'address'              => $this->address,
            'profile' => $this->profile
    ? config('filepaths.recruiter.public_url') . $this->profile
    : null,
            'branches' => Branch::whereIn(
    'id',
    $request->branch_ids ?? []
)->get(['id', 'name']),

        'specialization_id' => optional($this->specialization)->id,
        'specialization'    => optional($this->specialization)->name,

        // 'city'  => $this->city ?? '',
        // 'state' => $this->state ?? '',

        // 'location' => trim(
        //     ($this->city ?? '') .
        //     (!empty($this->city) && !empty($this->state) ? ', ' : '') .
        //     ($this->state ?? '')
        // ),

            'year_of_experiance' => $this->year_of_experiance ?? '0 Years',
            'hourly_rate'        => (string) ($this->hourly_rate ?? 0),
            'rating'             => (float) ($this->rating ?? 0),
            'review_count'       => (int) ($this->review_count ?? 0),

            'shift_date'        => $request->shift_date,
            'start_time'        => date('H:i:s', strtotime($request->start_time)),
            'end_time'          => date('H:i:s', strtotime($request->end_time)),
        ];
    }
}