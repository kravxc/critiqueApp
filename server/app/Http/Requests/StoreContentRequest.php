<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'music_type' => 'required|in:single,album,EP',
            'genre' => 'required|string|max:100',
            'release_date' => 'required|date',
            'label' => 'required|string|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'Название релиза обязательно для заполнения',
            'artist.required' => 'Исполнитель обязателен для заполнения',
            'music_type.required' => 'Тип релиза обязателен для выбора',
            'music_type.in' => 'Выберите корректный тип релиза',
            'genre.required' => 'Жанр обязателен для выбора',
            'release_date.required' => 'Дата релиза обязательна для заполнения',
            'release_date.date' => 'Укажите корректную дату релиза',
            'label.required' => 'Лейбл обязателен для заполнения',
            'cover_image.required' => 'Обложка релиза обязательна для загрузки',
            'cover_image.image' => 'Файл должен быть изображением',
            'cover_image.mimes' => 'Допустимые форматы: JPEG, PNG, JPG, WebP',
            'cover_image.max' => 'Максимальный размер файла: 2MB'
        ];
    }
}
