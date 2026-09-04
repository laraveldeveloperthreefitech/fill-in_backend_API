<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Traits\RestResponse;

class CreateJobRequest extends FormRequest
{
    use RestResponse;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Data for JobListing model
     */
    public function requestData()
    {
        return [

            'title'             => $this->title,

            'clinic_id'         => $this->user()->clinic->id,

            'specialization_id' => $this->profession,

            'address'           => $this->address,

            'short_address'     => $this->short_address,

            'salary_range_from' => $this->salary_range_from,

            'salary_range_to'   => $this->salary_range_to,

            'experiance_level'  => $this->experiance_level,

            'job_description'   => $this->job_description,

            'benefits'          => is_array($this->benefits)
                ? implode(',', $this->benefits)
                : null,

            'city'              => $this->city,

            'expire_date'       => $this->expire_date,

            'shift'             => is_array($this->shift)
                ? implode(',', $this->shift)
                : null,

            'vacancy'           => $this->vacancy,

            'latitude'          => $this->latitude,

            'longitude'         => $this->longitude,

            'urgent'            => $this->urgent ?? 0,

            'other_software'    => $this->other_software,
        ];
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        $recruiterId = auth()->guard('recruiter')->id();

        return [

            'id' => 'nullable|exists:job_listings,id',

            'title' => 'required|string|max:255',

            'profession' => 'required|exists:specializations,id',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'expire_date' => 'nullable|date_format:Y-m-d',

            /*
            |--------------------------------------------------------------------------
            | Branches
            |--------------------------------------------------------------------------
            */

            'branch_ids' => 'required|array|min:1',

            'branch_ids.*' => [

                'required',

                'integer',

                Rule::exists('branches', 'id')
                    ->where(function ($query) use ($recruiterId) {
                        $query->where('recruiter_id', $recruiterId);
                    }),

            ],

            /*
            |--------------------------------------------------------------------------
            | Benefits
            |--------------------------------------------------------------------------
            */

            'benefits' => 'nullable|array',

            'benefits.*' => 'string',

            /*
            |--------------------------------------------------------------------------
            | Shift
            |--------------------------------------------------------------------------
            */

            'shift' => 'nullable|array',

            'shift.*' => 'string',

            /*
            |--------------------------------------------------------------------------
            | Software
            |--------------------------------------------------------------------------
            */

            'software' => 'nullable|array',

            'software.*' => 'exists:software,id',

            'other_software' => 'nullable|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'address' => 'required|string',

            'short_address' => 'nullable|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | Salary
            |--------------------------------------------------------------------------
            */

            'salary_range_from' => 'nullable|numeric',

            'salary_range_to' => 'nullable|numeric',

            /*
            |--------------------------------------------------------------------------
            | Job
            |--------------------------------------------------------------------------
            */

            'experiance_level' => 'required|string|max:255',

            'job_description' => 'required|string',

            'city' => 'nullable|string|max:255',

            'vacancy' => 'required|integer|min:1',

            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            'latitude' => 'required',

            'longitude' => 'required',

            'urgent' => 'nullable|boolean',
        ];
    }

    /**
     * Validation Response
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->customValidationres(
                $validator->errors()->first()
            )
        );
    }
}