<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer|exists:roles,id',
            'trashed' => 'nullable|in:with,without,only',
            'sort_by' => 'nullable|in:name,email,created_at,updated_at,reviews_count',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'has_github' => 'nullable|boolean',
            'has_google' => 'nullable|boolean',
            'has_avatar' => 'nullable|boolean',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ];
    }

    public function messages()
    {
        return [
            'search.max' => 'Поисковый запрос не может превышать 255 символов',
            'role_id.exists' => 'Указанная роль не существует',
            'trashed.in' => 'Параметр trashed должен быть одним из: with, without, only',
            'sort_by.in' => 'Поле для сортировки должно быть одним из: name, email, created_at, updated_at, reviews_count',
            'sort_order.in' => 'Порядок сортировки должен быть asc или desc',
            'per_page.min' => 'Минимальное количество элементов на странице - 1',
            'per_page.max' => 'Максимальное количество элементов на странице - 100',
            'date_from.date' => 'Дата "от" должна быть корректной датой',
            'date_to.date' => 'Дата "до" должна быть корректной датой',
            'date_to.after_or_equal' => 'Дата "до" должна быть больше или равна дате "от"',
        ];
    }
}
