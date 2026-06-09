<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GitHubAuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'avatar' => $this->avatar,
                'bio' => $this->bio,
                'role' => $this->role ? $this->role->name : null,
                'github_id' => $this->github_id
            ],
            'token' => $this->when($this->token, $this->token)
        ];
    }
}
