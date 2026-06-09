<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string',
            'state' => 'required|string',
         ];
    }
    public function messages(): array
    {
        return [
            'code.required' => 'Authorization code is required',
            'state.required' => 'State parameter is required',
        ];
    }
}
