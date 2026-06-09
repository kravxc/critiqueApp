<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'content_id' => 'nullable|integer|exists:contents,id',
            'trashed' => 'nullable|in:with,without,only',
            'sort_by' => 'nullable|in:created_at,updated_at,title,likes_count',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100'
        ];
    }

    public function messages()
    {
        return [
            'search.max' => 'Поисковый запрос не может превышать 255 символов',
            'user_name.max' => 'Имя пользователя не может превышать 255 символов',
            'content_id.exists' => 'Указанный контент не существует',
            'trashed.in' => 'Параметр trashed должен быть одним из: with, without, only',
            'sort_by.in' => 'Поле для сортировки должно быть одним из: created_at, updated_at, title, likes_count',
            'sort_order.in' => 'Порядок сортировки должен быть asc или desc',
            'per_page.min' => 'Минимальное количество элементов на странице - 1',
            'per_page.max' => 'Максимальное количество элементов на странице - 100'
        ];
    }
}
