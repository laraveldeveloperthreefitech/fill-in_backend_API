<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function requestData(){
        return [
            'name'         => $this->name,
            'email'        => $this->email,
             'phone'       => $this->phone,
            'password'     => Hash::make($this->password), // Hash password
            'verified'     => 0, // Set false initially, modify as per your email verification logic
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
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:recruiters,email',
            'password'      => 'required|min:6', 
        ];
    }
}
