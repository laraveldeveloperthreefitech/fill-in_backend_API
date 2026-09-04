<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Recruiter;
use App\Models\Clinic;
use App\Models\Branch;
use App\Http\Resources\Recruiter\RecruiterResource;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email',
            'practice_name'     => 'nullable|string|max:255',
            'practice_role'     => 'nullable|integer',
            'practice_size'     => 'nullable|string',
            'primarly_looking'  => 'nullable|string',
            'established_year'  => 'nullable|digits:4',
            'address'           => 'nullable|string',
            'postcode'          => 'nullable|string|max:20',
            'description'       => 'nullable|string',
            'working_hours'     => 'nullable|array',
            'looking'           => 'nullable|array',
            'dentistry'         => 'nullable|array',
            'use_software'      => 'nullable|array',
            'branches'          => 'nullable|array',
            'branches.*'        => 'string|max:255',
            'profile'           => 'nullable|image',
            'document'          => 'nullable|file',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }

    public function requestData(): array
    {
        return $this->only([
            'name',
            'email',
            'phone',
            'profile',
        ]);
    }

    public function clinicData(): array
    {
        return [
            'name'               => $this->practice_name,
            'practice_role_id'   => $this->practice_role,
            'practice_size'      => $this->practice_size,
            'primarly_looking'   => $this->primarly_looking,
            'established_year'   => $this->established_year,
            'address'            => $this->address,
            'postcode'           => $this->postcode,
            'description'        => $this->description,
            'working_hours'      => $this->working_hours
                ? implode(',', $this->working_hours)
                : null,
            'other'              => $this->other,
            'other_software'     => $this->other_software,
            'web_link'           => $this->web_link,
            'location'           => $this->location,
        ];
    }
}