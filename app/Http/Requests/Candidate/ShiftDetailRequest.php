<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class ShiftDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('candidate')->check();
    }

    public function rules(): array
    {
        return [
            'shift_id' => 'required|exists:fillin_shifts,id',
        ];
    }
}