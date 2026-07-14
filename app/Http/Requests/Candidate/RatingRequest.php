<?php

namespace App\Http\Requests\Candidate;

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
            'recruiter_id' => $this->recruiter_id,
            'candidate_id' => auth()->user()->id,
            'rate'         => $this->rate,
            'comment'      => $this->comment,
            'image'        => $image,
        ];
    }

    public function rules(): array
    {
        return [
            'rate' => 'required|numeric|min:1|max:5',

            'recruiter_id' => [
                'required',
                'exists:recruiters,id',
                Rule::unique('clinic_reviews')->where(function ($query) {
                    return $query->where('candidate_id', auth()->user()->id);
                }),
            ],

            'comment' => 'nullable|string',
            'image'   => 'nullable',

            'answers' => 'nullable|array',

            'answers.*.question_id' => [
                'required_with:answers',
                'exists:clinic_feedback_questions,id',
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