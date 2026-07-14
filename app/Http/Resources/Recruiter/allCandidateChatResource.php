<?php

namespace App\Http\Resources\Recruiter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class allCandidateChatResource extends JsonResource
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
            'message'       => $this->message,
            'candidate_id'  => $this->canditidate_id,
            'msgStatus'     => $this->sendBy == "candidate" ? "recieve" : 'send',
            'time'          => $this->created_at->format('h:i A'),
            'candidate'     => $this->candidates ? $this->candidates->name : '',
            'status'    => $this->status ? 'seen' : 'unseen',
            'profile'       =>  $this->candidates && $this->candidates->profile ? config('filepaths.candidate.public_url') . $this->candidates->profile : '',
            'created_at'    => $this->created_at,
             'unseen_count'  => $this->unseen_count ?? 0,
        ];
    }
}
