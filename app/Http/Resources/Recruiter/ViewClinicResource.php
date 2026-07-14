<?php

namespace App\Http\Resources\Recruiter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewClinicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'rating'        => count($this->review) > 0 ? $this->review->sum('rate')/count($this->review) : 0,
            'reviews'       => !empty($this->review) ? $this->review->map(function($item){
                                return [
                                    'id'            => $item->id,
                                    'candidate_id'  => $item->candidate_id,
                                    'candidate_name'=> $item->candidate->name,
                                    'rate'          => $item->rate,
                                    'comment'       => $item->comment,
                                    'image'         => $item->image ? config('filepaths.clinic.public_url') . $item->image : null,
                                ];
                                }) : [],
            'email'         => $this->email,
            'logo'          => $this->logo ? config('filepaths.clinic.public_url') . $this->logo : null,
            'phone'         => $this->phone,
            'document_name' => $this->document_name,
            'document'      => $this->document ? config('filepaths.clinic.public_url') . $this->document : null,
            'bio'           => $this->bio,
            'address'       => $this->address,
            'verification'  => $this->verification,
            'created_at'    => date('Y-m-d H:i:s',strtotime($this->created_at)),
        ];
    }
}
