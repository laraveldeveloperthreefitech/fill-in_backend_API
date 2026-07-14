<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\RestResponse;

class OtpRequest extends FormRequest
{
    use RestResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function requestData($otp){
        return [
            'otp'        => $otp,
            'expire_otp' => now()->addMinutes(10)
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

        if (!auth()->guard('candidate')->check()) {
            $rules['email'] = 'required|email|exists:candidates,email';
        } elseif (isset($this->type) && $this->type === 'phone') {
            $rules['phone'] = 'required|unique:candidates,phone,';
        } else {
            $userId = auth()->guard('candidate')->id(); // Use candidate guard here
            $rules['email'] = 'required|email|unique:candidates,email,';
        }

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
