<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\{HelperTrait,RestResponse};
use Illuminate\Validation\Rule;

class JobReportRequest extends FormRequest
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
            'job_id'        => $this->job_id,
            'candidate_id'  => auth()->user()->id,
            'title'         => $this->title,
            'description'   => $this->description,
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
            'job_id'         => 'required|exists:job_listings,id',
            // 'job_id' => [
            //                     'required',
            //                     'exists:recruiters,id',
            //                     Rule::unique('report_on_jobs')->where(function ($query) {
            //                         return $query->where('candidate_id', auth()->user()->id);
            //                     }),
            //                 ],
                                
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
