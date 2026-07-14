<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\RestResponse;

class RegisterRequest extends FormRequest
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
        $language = $this->language ? implode(',',$this->language) : null;
        return [
            'name'         => $this->name,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'password'     => Hash::make($this->password),
        ];
    }

    public function educationData($id){
        $data = [];
        foreach($this->education as $key => $education){
            $data[] = [
                'candidate_id'   => $id,             
                'education_type' => $education['education_type'],
                'course'         => $education['course'],
                'course_type'    => $education['course_type'],
                'Specialization' => $education['Specialization'],
                'University'     => $education['University'],
                'start_year'     => $education['start_year'],
                'end_year'       => $education['end_year'],
                'grade'          => $education['grade']
            ];
        }
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required',
            'phone'         => 'required',
            'email'         => 'required|email|unique:candidates,email',
            'password'      => 'required|min:6',
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
