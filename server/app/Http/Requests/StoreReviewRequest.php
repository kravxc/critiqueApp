<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'text' => 'required|string|min:50',
            'content_id'=> 'required|integer|exists:contents,id',
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'Пожалуйста, укажите заголовок для вашей рецензии',
            'title.string' => 'Заголовок должен содержать только текст',
            'title.min' => 'Заголовок должен содержать минимум 5 символов',
            'title.max' => 'Заголовок слишком длинный. Максимум 255 символов',
            'text.required' => 'Напишите текст рецензии',
            'text.string' => 'Текст рецензии должен содержать только текст',
            'text.min' => 'Текст рецензии слишком короткий. Минимум 50 символов'
        ];
    }
}
