<?php

namespace App\Http\Resources\Recruiter;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Http\Resources\Json\JsonResource;

class RecruiterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [

            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'practice_name' => $this->clinic
                ? $this->clinic->name
                : '',

            'established_year' => $this->clinic
                ? $this->clinic->established_year
                : '',

            'practice_size' => $this->clinic
                ? $this->clinic->practice_size
                : '',

            'primarly_looking' => $this->clinic
                ? $this->clinic->primarly_looking
                : '',

            'working_hours' => $this->clinic
                ? explode(',', $this->clinic->working_hours)
                : [],

            'other_dentistry' => $this->clinic
                ? $this->clinic->other_dentistry
                : '',

            'other_practice_role' => $this->clinic
                ? $this->clinic->other_practice_role
                : '',

            'address' => $this->clinic
                ? $this->clinic->address
                : '',
                
                

             'branch' => $this->clinic && $this->clinic->branches
    ? $this->clinic->branches->pluck('name')->implode(', ')
    : '',

            'postcode' => $this->clinic
                ? $this->clinic->postcode
                : '',

            'phone' => $this->phone,

            'other_software' => $this->clinic
                ? $this->clinic->other_software
                : '',

            'other' => $this->clinic
                ? $this->clinic->other
                : '',

            /*
            |--------------------------------------------------------------------------
            | PROFILE IMAGE
            |--------------------------------------------------------------------------
            */

            'profile' => $this->profile
                ? config('filepaths.recruiter.public_url') . $this->profile
                : null,

            /*
            |--------------------------------------------------------------------------
            | DOCUMENT
            |--------------------------------------------------------------------------
            */

            'document' => $this->clinic
                ? (
                    $this->clinic->document
                    ? config('filepaths.clinic.public_url') . $this->clinic->document
                    : null
                )
                : null,

            'document_name' => $this->clinic
                ? $this->clinic->document_name
                : '',

            /*
            |--------------------------------------------------------------------------
            | REVIEWS
            |--------------------------------------------------------------------------
            */

            'reviews' => !empty($this->review)
                ? $this->review->map(function ($item) {

                    return [

                        'id' => $item->id,

                        'candidate_id' => $item->candidate_id,

                        'candidate_name' => $item->candidate
                            ? $item->candidate->name
                            : '',

                        'candidate_image' => $item->candidate
                            ? (
                                $item->candidate->profile
                                ? config('filepaths.candidate.public_url') . $item->candidate->profile
                                : null
                            )
                            : null,

                        'profession' => $item->candidate
                            ? $item->candidate->specialization_name
                            : '',

                        'rate' => $item->rate,

                        'comment' => $item->comment,

                        'image' => $item->image
                            ? config('filepaths.review.public_url') . $item->image
                            : null,

                        'created' => $item->created_at,

                    ];
                })
                : [],

            /*
            |--------------------------------------------------------------------------
            | EXTRA DETAILS
            |--------------------------------------------------------------------------
            */

            'description' => $this->clinic
                ? $this->clinic->description
                : '',

            'web_link' => $this->clinic
                ? $this->clinic->web_link
                : '',

            'rating' => count($this->review) > 0
                ? $this->review->sum('rate') / count($this->review)
                : 0,

            'review_count' => count($this->review),

            'phone_verified' => $this->phone_verified,

            'is_completed' => $this->clinic ? 1 : 0,

            'created_at' => date(
                'Y-m-d H:i:s',
                strtotime($this->created_at)
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | RECRUITER LOGIN
        |--------------------------------------------------------------------------
        */

        if (auth()->guard('recruiter')->check()) {

            $data['practice_role'] = $this->clinic
                ? $this->clinic->practice_role_id
                : '';

            $data['looking'] = !empty($this->lookingFor)
                ? $this->lookingFor->pluck('id')
                : [];

            $data['dentistry'] = !empty($this->dentistryPractices)
                ? $this->dentistryPractices->pluck('id')
                : [];

            $data['use_software'] = !empty($this->useSoftware)
                ? $this->useSoftware->pluck('id')
                : [];
        }

        /*
        |--------------------------------------------------------------------------
        | OTHER USERS
        |--------------------------------------------------------------------------
        */

        else {

            $data['practice_role'] = $this->clinic
                ? (
                    $this->clinic->practiceRole
                    ? $this->clinic->practiceRole->name
                    : ''
                )
                : '';

            $data['looking'] = !empty($this->lookingFor)
                ? $this->lookingFor->pluck('name')
                : [];

            $data['dentistry'] = !empty($this->dentistryPractices)
                ? $this->dentistryPractices->pluck('name')
                : [];

            $data['use_software'] = !empty($this->useSoftware)
                ? $this->useSoftware->pluck('name')
                : [];

            /*
            |--------------------------------------------------------------------------
            | Candidate Login
            |--------------------------------------------------------------------------
            */

            if (auth()->guard('candidate')->check()) {

                $candidateId = auth()->guard('candidate')->user()->id;

                $data['is_review'] = !empty($this->review)
                    ? (
                        $this->review
                        ->where('candidate_id', $candidateId)
                        ->first()
                        ? 1
                        : 0
                    )
                    : 0;

                $data['is_schedule'] = $this->clinic
                    && count($this->clinic->interview) > 0
                    ? (
                        $this->clinic->interview
                        ->where('candidate_id', $candidateId)
                        ->where('status', 1)
                        ->first()
                        ? 1
                        : 0
                    )
                    : 0;
            }
        }

        return $data;
    }
}