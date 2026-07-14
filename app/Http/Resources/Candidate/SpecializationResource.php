<?php

namespace App\Http\Resources\Candidate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecializationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'job_count'         => $this->job_count,
            'icon'              => $this->logo ? config('filepaths.profession.public_url') . $this->logo : '',
            'candidate_count'   => $this->candidate_count,
            'created_at'        => $this->created_at,
        ];
    }
}
