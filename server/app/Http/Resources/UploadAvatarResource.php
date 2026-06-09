<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UploadAvatarResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'message' => 'Аватар успешно загружен',
                'avatar_url' => $this->avatar_url,
                'user' => [
                    'id' => $this->id,
                    'name' => $this->name,
                    'email' => $this->email,
                    'role' => $this->role->name,
                    'bio' => $this->bio,
                    'preferences' => $this->preferences,
                ]
            ]
        ];
    }
}
