<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\{HelperTrait, RestResponse};
use Illuminate\Validation\Rule;

class RatingRequest extends FormRequest
{
    use HelperTrait, RestResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function requestData()
    {
        $image = null;

        if ($this->image) {
            $image = $this->imageUploadToBase64(
                config('filepaths.review.directory'),
                $this->image
            );
        }

        return [
            'clinic_id'    => auth()->user()->clinic->id,
            'candidate_id' => $this->candidate_id,
            'rate'         => $this->rate,
            'comment'      => $this->comment,
            'image'        => $image,
        ];
    }

    public function rules(): array
    {
        return [
            'rate' => 'required|numeric|min:1|max:5',

            'candidate_id' => [
                'required',
                'exists:candidates,id',
                Rule::unique('candidate_reviews')->where(function ($query) {
                    return $query->where('clinic_id', auth()->user()->clinic->id);
                }),
            ],

            'comment' => 'nullable|string',
            'image'   => 'nullable',

            'answers' => 'nullable|array',

            'answers.*.question_id' => [
                'required_with:answers',
                'exists:candidate_feedback_questions,id',
            ],

            'answers.*.answer' => 'nullable|string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                $this->customValidationres($validator->errors()->first())
            );
        }

        parent::failedValidation($validator);
    }
}