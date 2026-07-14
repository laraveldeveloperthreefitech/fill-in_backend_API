<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\HelperTrait;
use App\Models\Recruiter;

class RecruiterRequest extends FormRequest
{
    use HelperTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
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
        $recruiter = Recruiter::find($this->id);

        // old image
        $path = $recruiter ? $recruiter->profile : null;

        /*
        |--------------------------------------------------------------------------
        | PROFILE IMAGE UPLOAD
        |--------------------------------------------------------------------------
        */

        if ($this->hasFile('profile')) {

            $file = $this->file('profile');

            // unique image name
            $imageName = time() . '_' . $file->getClientOriginalName();

            // image move
            $file->move(
                public_path('storage/images/recruiter'),
                $imageName
            );

            // save name in db
            $path = $imageName;
        }

        return [

            'name'    => $this->name,

            'email'   => $this->email,

            'profile' => $path,
        ];
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [

            'name'    => 'required|string|max:255',

            'email'   => 'required|email',

            'profile' => 'nullable|image|mimes:jpg,jpeg,png,svg',
        ];
    }
}