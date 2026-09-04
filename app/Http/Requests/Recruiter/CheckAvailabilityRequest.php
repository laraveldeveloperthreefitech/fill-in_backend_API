<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_id'     => 'required|exists:fillin_shifts,id',
            'candidate_id' => 'required|exists:candidates,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }
}