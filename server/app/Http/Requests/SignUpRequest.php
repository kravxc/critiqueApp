<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string:max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'terms' => 'required|accepted',
            'role_id' => 'integer|exists:roles,id'
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Поле "Имя" обязательно для заполнения.',
            'email.required' => 'Поле "Email" обязательно для заполнения.',
            'email.email' => 'Введите корректный email адрес.',
            'email.unique' => 'Пользователь с таким email уже существует.',
            'password.required' => 'Поле "Пароль" обязательно для заполнения.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'terms.required' => 'Необходимо принять условия использования.',
            'terms.accepted' => 'Необходимо принять условия использования.',
        ];
    }
}
