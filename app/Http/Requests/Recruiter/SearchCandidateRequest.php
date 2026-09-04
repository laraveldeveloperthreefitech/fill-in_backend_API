<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'title'             => 'required|string|max:255',
            'specialization_id' => 'required|exists:specializations,id',
            'experiance_level'  => 'nullable|string|max:255',

            'software'          => 'nullable|array',
            'software.*'        => 'integer|exists:software,id',
            'other_software'    => 'nullable|string|max:255',

            'vacancy'           => 'nullable|integer',
            'urgent'            => 'nullable|boolean',

            'city'              => 'nullable|string|max:255',
            'address'           => 'nullable|string',
            'short_address'     => 'nullable|string|max:255',

            'job_description'   => 'nullable|string',
            'expire_date'       => 'nullable|date',

            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',

            'hourly_rate'       => 'required|numeric',

            'shift_date'        => 'required|date',

            'start_time'        => 'required',

            'end_time'          => 'required',

            'branch_ids'        => 'nullable|array',

            'branch_ids.*'      => 'integer|exists:branches,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(

            response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ],422)

        );
    }
}