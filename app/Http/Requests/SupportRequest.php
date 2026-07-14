<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\RestResponse;

class SupportRequest extends FormRequest
{

    use RestResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function requestClinic(){
        return [
            'recruiter_id'  => auth()->guard('recruiter')->user()->id,
            'message'       => $this->message
        ];
    }
    public function requestCandidate(){
        return [
            'candidate_id'  => auth()->user()->id,
            'message'       => $this->message
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => 'required'
        ];
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
