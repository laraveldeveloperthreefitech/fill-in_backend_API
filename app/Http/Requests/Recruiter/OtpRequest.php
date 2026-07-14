<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OtpRequest extends FormRequest
{
    /**
     * Authorize Request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * OTP Save Data
     */
    public function requestData($otp): array
    {
        return [
            'otp'        => $otp,
            'expire_otp' => now()->addMinutes(10),
        ];
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        $rules = [

            'type' => 'required|in:email,phone',
        ];





        /*
        |--------------------------------------------------------------------------
        | USER NOT LOGGED IN
        |--------------------------------------------------------------------------
        */

        if (!auth()->guard('recruiter')->check()) {

            // EMAIL OTP
            if ($this->type === 'email') {

                $rules['email'] = [
                    'required',
                    'email',
                    'exists:recruiters,email'
                ];
            }

            // PHONE OTP
            if ($this->type === 'phone') {

                $rules['phone'] = [
                    'required',
                    'exists:recruiters,phone'
                ];
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | USER LOGGED IN
            |--------------------------------------------------------------------------
            */

            $userId = auth()->guard('recruiter')->id();




            // UPDATE EMAIL
            if ($this->type === 'email') {

                $rules['email'] = [
                    'required',
                    'email',
                    'unique:recruiters,email,' . $userId
                ];
            }




            // UPDATE PHONE
            if ($this->type === 'phone') {

                $rules['phone'] = [
                    'required',
                    'unique:recruiters,phone,' . $userId
                ];
            }
        }

        return $rules;
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'type.required' => 'Type field is required.',
            'type.in'       => 'Type must be email or phone.',

            'email.required' => 'Email field is required.',
            'email.email'    => 'Please enter valid email.',
            'email.exists'   => 'This email is not registered.',
            'email.unique'   => 'This email already exists.',

            'phone.required' => 'Phone field is required.',
            'phone.exists'   => 'This phone number is not registered.',
            'phone.unique'   => 'This phone number already exists.',
        ];
    }

    /**
     * Validation Failed Response
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(

            response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422)

        );
    }
}