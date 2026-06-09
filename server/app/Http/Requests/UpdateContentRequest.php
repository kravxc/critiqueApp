<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'artist' => 'nullable|string|max:255',
            'music_type' => 'nullable|in:single,album,EP',
            'genre' => 'nullable|string|max:100',
            'release_date' => 'nullable|date',
            'label' => 'nullable|string|max:255',
        ];
    }
}
