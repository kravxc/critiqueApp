<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GitHubCallbackRequest extends FormRequest
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
          'code.required' => 'Код авторизации обязателен',
            'state.required' => 'State параметр обязателен'
        ];
    }
}
