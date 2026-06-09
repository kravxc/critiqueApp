<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditProfileRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'bio' => 'nullable|string',
            'name' => 'nullable|string',
            'email' => 'nullable|string',
        ];
    }
}
