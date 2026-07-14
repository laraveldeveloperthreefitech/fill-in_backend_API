<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\{HelperTrait,RestResponse};

class CandidateReportRequest extends FormRequest
{
    use HelperTrait,RestResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function requestData(){
        $image    = null;
        if($this->image){
            $image = $this->imageUploadToBase64(config('filepaths.report.directory'),$this->image);
        }
        return [
            'clinic_id'     => auth()->user()->clinic->id,
            'candidate_id'  => $this->candidate_id,
            'title'         => $this->title,
            'description'   =>  $this->description,
            'image'         => $image,
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
            'title'          => 'required',
            'description'    => 'required',
            'candidate_id'   => 'required|exists:candidates,id',
                                
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
