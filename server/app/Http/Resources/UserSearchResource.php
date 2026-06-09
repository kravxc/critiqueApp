<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentUser = auth()->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ],
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'reviews_count' => $this->reviews_count ?? $this->reviews()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'socials' => [
                'github' => (bool)$this->github_id,
                'google' => (bool)$this->google_id,
            ],
            'has_avatar' => !is_null($this->avatar),
            'is_trashed' => !is_null($this->deleted_at),

            'admin_info' => $currentUser && $currentUser->role_id == 1 ? [
                'github_id' => $this->github_id,
                'google_id' => $this->google_id,
                'preferences' => $this->preferences,
                'email_verified_at' => $this->email_verified_at,
            ] : null,

            'can_edit' => $currentUser && ($currentUser->id == $this->id || $currentUser->role_id == 1),
            'can_delete' => $currentUser && $currentUser->role_id == 1 && $this->id != 1,
            'can_restore' => $currentUser && $currentUser->role_id == 1 && !is_null($this->deleted_at),
            'can_force_delete' => $currentUser && $currentUser->role_id == 1 && !is_null($this->deleted_at),
        ];
    }
}
