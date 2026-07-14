<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message'   => $this->message,
            'msgStatus' => auth()->guard('candidate')->check() && $this->sendBy == 'candidate' ? 'send' : (auth()->guard('recruiter')->check() && $this->sendBy == 'recruiter' ? 'send' : 'recieve'),
           'time' => $this->created_at->format('h:i A'),

            'status'    => $this->status ? 'seen' : 'unseen'
        ];
    }
}
