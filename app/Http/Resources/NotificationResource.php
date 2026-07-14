<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    	if($this->data && isset($this->data['Imagetype']) == 'candidate'){
        	$url = config('filepaths.candidate.public_url');
        }else{
        	$url = config('filepaths.recruiter.public_url');
        }
        return [
        	'id'			=> $this->id,
        	'title'			=> $this->data ? isset($this->data['title']) ? $this->data['title'] : ''  : '',
        	'message'		=> $this->data ? isset($this->data['message']) ? $this->data['message'] : ''  : '',
        	'redirect_url'	=> $this->data ? isset($this->data['redirect_url']) ? $this->data['redirect_url'] : ''  : '',
            'icone'	        => $this->data ? isset($this->data['icon']) ? $url . $this->data['icon'] : ''  : '',
            'type'	        => $this->data ? isset($this->data['type']) ? $this->data['type'] : ''  : '',
            'uniqe_id'	    => $this->data ? isset($this->data['uniqe_id']) ? $this->data['uniqe_id'] : ''  : '',
        	'read_at'		=> $this->read_at,
        	'is_read'		=> $this->is_read,
             'created_at'   => $this->created_at,
        	
        ];
    }
}
