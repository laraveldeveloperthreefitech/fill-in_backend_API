<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\RestResponse;
use App\Http\Traits\HelperTrait;
use App\Models\Clinic;

class CreateClinicRequest extends FormRequest
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
        $clinic   = Clinic::where('id',$this->id)->first();
        $document = $clinic ? $clinic ->document : null;
        $logo     = $clinic ? $clinic ->logo : null;
        if($this->document){
            $document =  $this->pdfUploadToBase64(config('filepaths.clinic.directory'),$this->document);
        }
        if($this->logo){
            $logo = $this->imageUploadToBase64(config('filepaths.clinic.directory'),$this->logo);
        }
        
        return [
            'name'              =>  $this->name,
            'recruiter_id'      =>  $this->user()->id,
            'email'             =>  $this->email,
            'phone'             =>  $this->phone,
            'document_name'     =>  $this->document_name,
            'document'          =>  $document,
            'bio'               =>  $this->bio,
            'address'           =>  $this->location,
            'logo'              =>  $logo,
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
            'id'                => 'nullable|exists:clinics,id',
            'email'             => 'required',
            'phone'             => 'required',
            'bio'               => 'required',
            'document'          => 'required',
            'location'          =>  'required'
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
