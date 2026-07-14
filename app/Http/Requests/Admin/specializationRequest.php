<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Specialization;
use App\Http\Traits\HelperTrait;

class specializationRequest extends FormRequest
{
    use HelperTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function requestData(){
        $path = Specialization::where('id',$this->id)->pluck('logo')->first();
        if($this->hasfile('image')){
            $path = $this->imageUpload(config('filepaths.profession.directory'),$this->image);
        }
        return [
            'name'    => $this->name,
            'logo'    => $path
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
            'id' => 'nullable|exists:specializations',
            'name' => 'required',
        ];
    }
}
