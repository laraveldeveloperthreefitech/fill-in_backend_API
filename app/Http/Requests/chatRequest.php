<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\{HelperTrait,RestResponse};

class chatRequest extends FormRequest
{
    use RestResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function requestData(){
        return [
            'canditidate_id' => auth()->guard('candidate')->check() ? auth()->guard('candidate')->user()->id : $this->candidate_id,
            'recruiter_id'   => auth()->guard('recruiter')->check()  ? auth()->guard('recruiter')->user()->id : $this->recruiter_id,
            'sendBy'         => auth()->guard('candidate')->check() ? 'candidate' : 'recruiter',
            'message'        => $this->message,
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {   
        $rules = [];
        if(auth()->guard('candidate')->check()){
            $rules['recruiter_id'] = 'required|exists:recruiters,id';
        }
        if(auth()->guard('recruiter')->check()){
            $rules['candidate_id'] = 'required|exists:candidates,id';
        }
        $rules['message'] = 'required'; 
        return $rules;
    }

      public function failedValidation(Validator $validator)
        {
            if ($this->expectsJson()) {
                // For API requests
                throw new HttpResponseException($this->customValidationres($validator->errors()->first()));
            }
        
            // For Blade (web form) requests – use Laravel's default behavior
            parent::failedValidation($validator);
        }
}
