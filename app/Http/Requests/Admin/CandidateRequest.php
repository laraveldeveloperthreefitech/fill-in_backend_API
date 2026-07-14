<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Candidate;

class CandidateRequest extends FormRequest
{
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
        $candidate = Candidate::find($this->id);

        // old image
        $path = $candidate ? $candidate->profile : null;

        /*
        |--------------------------------------------------------------------------
        | PROFILE IMAGE UPLOAD
        |--------------------------------------------------------------------------
        */

        if ($this->hasFile('profile')) {

            $file = $this->file('profile');

            // unique image name
            $imageName = time() . '_' . $file->getClientOriginalName();

            // move image
            $file->move(
                public_path('storage/images/candidate'),
                $imageName
            );

            // save image name
            $path = $imageName;
        }

        return [

            'name'    => $this->name,

            'email'   => $this->email,

            'profile' => $path,
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

            'name'    => 'required|string|max:255',

            'email'   => 'required|email',

            'profile' => 'nullable|image|mimes:jpg,jpeg,png,svg',
        ];
    }
}