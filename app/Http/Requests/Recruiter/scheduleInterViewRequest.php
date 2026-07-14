<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\{HelperTrait,RestResponse};
use Carbon\Carbon;

class scheduleInterViewRequest extends FormRequest
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
        return [
            'title'        => $this->title,
            'candidate_id'  => $this->candidate_id,
            'clinic_id'     => auth()->user()->clinic->id,
            'job_id'        => $this->job_id,
            'date'          => $this->date,
            'time'          => $this->time,
            'end_time'      => $this->end_time,
            'link'          => $this->link,
            'notes'         => $this->notes,
            'timezone'      => $this->header('timezone'),
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
            'id'           => 'nullable|exists:schedule_interviews,id',
            'candidate_id' => 'required|exists:candidates,id',
            'job_id'       => 'required|exists:job_listings,id',
            'date'         => 'required|date_format:Y-m-d|after_or_equal:today',
            'time'         => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:time',
            'link'         => 'required|url',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $date = $this->input('date');
            $time = $this->input('time');
			
            if ($date === now()->format('Y-m-d') && $time) {
                $inputDateTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
                if ($inputDateTime->lt(now())) {
                    $validator->errors()->add('time', 'The interview time must be in the future.');
                }
            }
        });
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
