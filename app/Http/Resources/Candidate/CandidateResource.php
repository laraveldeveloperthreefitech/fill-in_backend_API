<?php

namespace App\Http\Resources\Candidate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | BASIC DETAILS
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'phone' => $this->phone,

            'location' => $this->address,

            /*
            |--------------------------------------------------------------------------
            | PROFILE IMAGE
            |--------------------------------------------------------------------------
            */

            'profile' => $this->profile
                ? config('filepaths.candidate.public_url') . $this->profile
                : null,

             'before_image' => $this->before_image
    ? collect(json_decode($this->before_image, true))
        ->map(function ($image) {
            return config('filepaths.candidate.before_url') . $image;
        })
        ->values()
        ->toArray()
    : [],

'after_image' => $this->after_image
    ? collect(json_decode($this->after_image, true))
        ->map(function ($image) {
            return config('filepaths.candidate.after_url') . $image;
        })
        ->values()
        ->toArray()
    : [],
            /*
            |--------------------------------------------------------------------------
            | DOCUMENT FILE URL
            |--------------------------------------------------------------------------
            */

            'document' => $this->document
                ? config('filepaths.candidate.document_url') . $this->document
                : null,

            'document_name' => $this->document_name,

            /*
            |--------------------------------------------------------------------------
            | EXPERIENCE
            |--------------------------------------------------------------------------
            */

            'type_of_experiance' => $this->type_of_experiance,

            'year_of_experiance' => $this->year_of_experiance,

            'hourly_rate' => $this->hourly_rate,

            'radius' => $this->radius,

            /*
            |--------------------------------------------------------------------------
            | EXTRA DETAILS
            |--------------------------------------------------------------------------
            */

            'other_qualification' => $this->other_qualification,

            'other_software' => $this->other_software,
            'other' => $this->other,

            'other_vaccination' => $this->other_vaccination,

            'short_notice' => $this->short_notice,

            'permanent_opportunities' => $this->permanent_opportunities,

            'childrens_check' => $this->childrens_check,

            'valid_police_check' => $this->valid_police_check,

            'first_aid_certicate' => $this->first_aid_certicate,

            'working_in_dentistry' => $this->working_in_dentistry,

            'fun_fact' => $this->fun_fact,

            'profession' => $this->specialization_name,

            /*
            |--------------------------------------------------------------------------
            | JSON DATA
            |--------------------------------------------------------------------------
            */

            'availability_time' => $this->availability_time
                ? json_decode($this->availability_time, true)
                : [],

            'environment_thrive' => $this->environment_thrive
                ? json_decode($this->environment_thrive, true)
                : [],

            'candidate_availibity' => $this->candidate_availibity
                ? json_decode($this->candidate_availibity, true)
                : [],

            /*
            |--------------------------------------------------------------------------
            | RELATIONS
            |--------------------------------------------------------------------------
            */

            'language' => $this->languages
                ? $this->languages->pluck('id')
                : [],

            // 'software_experiance' => $this->software_experiance
            //     ? $this->software_experiance->pluck('id')
            //     : [],
            
            'software_experiance' => $this->software_experiance
    ? $this->software_experiance->map(function ($item) {
        return [
            'id'   => $item->id,
            'name' => $item->name,
        ];
    })->values()
    : [],

            // 'qualification' => $this->qualification
            //     ? $this->qualification->pluck('id')
            //     : [],
            
            'qualification' => $this->qualification
    ? $this->qualification->map(function ($item) {
        return [
            'id'   => $item->id,
            'name' => $item->name,
        ];
    })->values()
    : [],

            // 'vaccination' => $this->Vaccination
            //     ? $this->Vaccination->pluck('id')
            //     : [],
            
            'vaccination' => $this->Vaccination
    ? $this->Vaccination->map(function ($item) {
        return [
            'id'   => $item->id,
            'name' => $item->name,
        ];
    })->values()
    : [],

            /*
            |--------------------------------------------------------------------------
            | RATINGS
            |--------------------------------------------------------------------------
            */

            'rating' => $this->review && count($this->review) > 0
                ? round(
                    $this->review->sum('rate') / count($this->review),
                    1
                )
                : 0,

            'review_count' => $this->review
                ? count($this->review)
                : 0,

            /*
            |--------------------------------------------------------------------------
            | REVIEWS
            |--------------------------------------------------------------------------
            */

            'reviews' => $this->review
                ? $this->review->map(function ($item) {

                    return [

                        'id' => $item->id,

                        'recruiter_id' => $item->clinic
                            ? $item->clinic->recruiter_id
                            : null,

                        'recruiter_name' => $item->clinic &&
                            $item->clinic->recruiter
                            ? $item->clinic->recruiter->name
                            : null,

                        'clinic_name' => $item->clinic
                            ? $item->clinic->name
                            : null,

                        'clinic_profile' => $item->clinic
                            ? (
                                $item->clinic->profile
                                ? config('filepaths.clinic.public_url') . $item->clinic->profile
                                : null
                            )
                            : null,

                        'rate' => $item->rate,

                        'comment' => $item->comment,

                        'image' => $item->image
                            ? config('filepaths.review.public_url') . $item->image
                            : null,

                        'created' => $item->created_at
                            ? date(
                                'Y-m-d H:i:s',
                                strtotime($item->created_at)
                            )
                            : null,
                    ];
                })
                : [],

            /*
            |--------------------------------------------------------------------------
            | VERIFICATION
            |--------------------------------------------------------------------------
            */

            'phone_verified' => $this->phone_verified,
        ];
    }
}