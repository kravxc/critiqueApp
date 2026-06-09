<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleLikeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'like_type' => 'required|in:like,author_like',
        ];
    }
}
