<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class RecruiterReportRequest extends FormRequest
{
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
            'recruiter_id'  => $this->recruiter_id,
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
            'recruiter_id'   => 'required|exists:recruiters,id',
        ];
    }
}
