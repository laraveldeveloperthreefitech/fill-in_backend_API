<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModifyChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    // dd($this->recruiters->clinic);
        return [
            'id'    => $this->id,
            'message'   => $this->message,
            'recruiter_id'  => $this->recruiter_id,
            'msgStatus'    => $this->sendBy == "candidate" ? "send" : 'recieve',
            'time' => $this->created_at->format('h:i A'),
            'status'    => $this->status ? 'seen' : 'unseen',
            'profile'       =>  $this->recruiters && $this->recruiters->clinic && $this->recruiters->clinic->profile ? config('filepaths.recruiter.public_url') . $this->recruiters->clinic->profile : '',
            'recruiter' => $this->recruiters ? $this->recruiters->name : '',
            'created_at'    => $this->created_at,
            'unseen_count'  => $this->unseen_count ?? 0,
        ];
    }
}
