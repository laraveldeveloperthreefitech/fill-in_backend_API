<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | REQUEST DATA
    |--------------------------------------------------------------------------
    */

    public function requestData()
    {
        $candidate = auth()->guard('candidate')->user();

        /*
        |--------------------------------------------------------------------------
        | OLD FILES
        |--------------------------------------------------------------------------
        */

        $profile  = $candidate->profile;
        $document = $candidate->document;

        /*
        |--------------------------------------------------------------------------
        | PROFILE IMAGE UPLOAD
        |--------------------------------------------------------------------------
        */

        if ($this->hasFile('profile')) {

            $file = $this->file('profile');

            $profile = time() . '_' . $file->getClientOriginalName();

            $file->move(
                public_path('storage/images/candidate'),
                $profile
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT UPLOAD
        |--------------------------------------------------------------------------
        */

        if ($this->hasFile('document')) {

            $file = $this->file('document');

            $document = time() . '_' . $file->getClientOriginalName();

            $file->move(
                public_path('storage/documents/candidate'),
                $document
            );
        }

        

        $before_image  = $candidate->before_image;
        $after_image = $candidate->after_image;

        /*
        |--------------------------------------------------------------------------
        | PROFILE IMAGE UPLOAD
        |--------------------------------------------------------------------------
        */

        $beforeImages = [];

if ($this->hasFile('before_image')) {

    foreach ($this->file('before_image') as $file) {

        $name = time().'_before_'.uniqid().'.'.$file->getClientOriginalExtension();

        $file->move(
            public_path('storage/images/candidate/before'),
            $name
        );

        $beforeImages[] = $name;
    }
} else {
    $beforeImages = $candidate->before_image
        ? json_decode($candidate->before_image, true)
        : [];
}
        

        $afterImages = [];

if ($this->hasFile('after_image')) {

    foreach ($this->file('after_image') as $file) {

        $name = time().'_after_'.uniqid().'.'.$file->getClientOriginalExtension();

        $file->move(
            public_path('storage/images/candidate/after'),
            $name
        );

        $afterImages[] = $name;
    }

} else {

    $afterImages = $candidate->after_image
        ? json_decode($candidate->after_image, true)
        : [];
}

        return [

            'name' => $this->name,

            'email' => $this->email,

            'phone' => $this->phone,

            'address' => $this->location,

            'profile' => $profile,

            'before_image' => json_encode($beforeImages),
            'after_image' => json_encode($afterImages),

            'document' => $document,

            'document_name' => $this->document_name,

            'type_of_experiance' => $this->type_of_experiance
                ? strtolower($this->type_of_experiance)
                : null,

            'other_qualification' => $this->other_qualification,

            'other_software' => $this->other_software,
            'other' => $this->other,

            'other_vaccination' => $this->other_vaccination,

            'year_of_experiance' => $this->year_of_experiance,

            'hourly_rate' => $this->hourly_rate,

            'radius' => $this->radius,

            'availability_time' => $this->availability_time
                ? json_encode($this->availability_time)
                : null,

            'short_notice' => $this->short_notice,

            'permanent_opportunities' => $this->permanent_opportunities,

            'childrens_check' => $this->childrens_check,

            'valid_police_check' => $this->valid_police_check,

            'first_aid_certicate' => $this->first_aid_certicate,

            'working_in_dentistry' => $this->working_in_dentistry,

            'environment_thrive' => $this->environment_thrive
                ? json_encode($this->environment_thrive)
                : null,

            'fun_fact' => $this->fun_fact,

            'specialization_name' => $this->profession,

            'latitude' => $this->latitude,

            'longitude' => $this->longitude,

            'candidate_availibity' => $this->candidate_availibity
                ? json_encode($this->candidate_availibity)
                : null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION RULES
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        return [

            'name' => 'required|string|max:255',

            'email' => [

                'required',

                'email',

                Rule::unique('candidates')
                    ->ignore(auth()->guard('candidate')->id())
            ],

            'phone' => 'nullable|string|max:20',

            'profile' => 'nullable|image|mimes:jpg,jpeg,png,svg',

            'document' => 'nullable',

            'other' => 'nullable|string|max:255',

            'before_image' => 'nullable|array',
            'before_image.*' => 'image|mimes:jpg,jpeg,png|max:2048',

            'after_image' => 'nullable|array',
            'after_image.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION RESPONSE
    |--------------------------------------------------------------------------
    */

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(

            response()->json([

                'status' => false,

                'message' => $validator->errors()->first()

            ], 422)
        );
    }
}