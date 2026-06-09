<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }
        
        $isTrashed = $this->trashed();
        return [
            'id' => $this->id,
            'title' => $this->title ?? '',
            'text' => $this->text ?? '',
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            'can_restore' => $isTrashed, 
            'can_force_delete' => $isTrashed, 
            'is_trashed' => $this->trashed(), 
            'user' => $this->getUserData(),

            'content' => $this->getContentData(),

            'likes' => [
                'total' => $this->total_likes_count ?? 0,
                'breakdown' => [
                    'like' => $this->regular_likes_count ?? 0,
                    'author_like' => $this->author_likes_count ?? 0,
                ],
                'details' => [
                    'regular_likes' => $this->getRegularLikes(),
                    'author_likes' => $this->getAuthorLikes(),
                ],
            ],
        ];
    }

    protected function getUserData(): array
    {
        if (!$this->relationLoaded('user') || $this->user === null) {
            return [
                'id' => null,
                'name' => 'Удаленный пользователь',
                'email' => '',
                'avatar' => null,
                'role_id' => 2,
                'avatar_url' => null,
            ];
        }

        return [
            'id' => $this->user->id,
            'name' => $this->user->name ?? 'Без имени',
            'email' => $this->user->email ?? '',
            'avatar' => $this->user->avatar ?? null,
            'role_id' => $this->user->role_id ?? 2,
            'avatar_url' => $this->user->avatar_url ?? null,
        ];
    }

    protected function getContentData(): ?array
    {
        if (!$this->relationLoaded('content') || $this->content === null) {
            return null;
        }

        return [
            'id' => $this->content->id,
            'title' => $this->content->title ?? '',
            'artist' => $this->content->artist ?? '',
            'cover_image' => $this->content->cover_image ?? null,
            'cover_image_url' => $this->content->cover_image_url ?? null,
        ];
    }

    protected function getRegularLikes(): array
    {
        if (!$this->relationLoaded('regularLikes')) {
            return [];
        }

        return $this->regularLikes->map(function ($like) {
            return [
                'id' => $like->id,
                'user_id' => $like->user_id,
                'type' => $like->type,
                'created_at' => $like->created_at?->toISOString(),
                'user' => $like->user ? [
                    'id' => $like->user->id,
                    'name' => $like->user->name ?? '',
                    'email' => $like->user->email ?? '',
                    'avatar' => $like->user->avatar ?? null,
                ] : null,
            ];
        })->toArray();
    }

    protected function getAuthorLikes(): array
    {
        if (!$this->relationLoaded('authorLikes')) {
            return [];
        }

        return $this->authorLikes->map(function ($like) {
            return [
                'id' => $like->id,
                'user_id' => $like->user_id,
                'type' => $like->type,
                'created_at' => $like->created_at?->toISOString(),
                'user' => $like->user ? [
                    'id' => $like->user->id,
                    'name' => $like->user->name ?? '',
                    'email' => $like->user->email ?? '',
                    'avatar' => $like->user->avatar ?? null,
                ] : null,
            ];
        })->toArray();
    }
}