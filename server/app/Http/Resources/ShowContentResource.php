<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'content' => [
                'id' => $this->id,
                'title' => $this->title,
                'music_type' => $this->music_type,
                'artist' => $this->artist,
                'genre' => $this->genre,
                'release_date' => $this->release_date,
                'cover_image' => $this->cover_image,
                'cover_image_url' => $this->cover_image_url,
                'label' => $this->label,
                'favorites_count' => $this->favorites_count,
                'reviews_count' => $this->reviews_count,
                'added_by' => $this->added_by,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'deleted_at' => $this->deleted_at,
            ],
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'title' => $review->title,
                        'text' => $review->text,
                        'likes' => [
                            'total' => $review->total_likes_count ?? 0,
                            'breakdown' => [
                                'like' => $review->regular_likes_count ?? 0,
                                'author_like' => $review->author_likes_count ?? 0,
                            ],
                            'details' => [
                                'regular_likes' => $review->regularLikes->map(function ($like) {
                                    return [
                                        'id' => $like->id,
                                        'user_id' => $like->user_id,
                                        'type' => $like->type,
                                        'user' => $like->user ? [
                                            'id' => $like->user->id,
                                            'name' => $like->user->name,
                                            'email' => $like->user->email,
                                            'role_id' => $like->user->role_id,
                                        ] : null,
                                        'created_at' => $like->created_at,
                                    ];
                                }),
                                'author_likes' => $review->authorLikes->map(function ($like) {
                                    return [
                                        'id' => $like->id,
                                        'user_id' => $like->user_id,
                                        'type' => $like->type,
                                        'user' => $like->user ? [
                                            'id' => $like->user->id,
                                            'name' => $like->user->name,
                                            'email' => $like->user->email,
                                            'role_id' => $like->user->role_id,
                                        ] : null,
                                        'created_at' => $like->created_at,
                                    ];
                                }),
                            ],
                        ],
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
                        'user' => [
                            'id' => $review->user->id ?? null,
                            'name' => $review->user->name ?? null,
                            'email' => $review->user->email ?? null,
                            'avatar' => $review->user->avatar ?? null,
                            'role_id' => $review->user->role_id ?? null,
                        ],
                    ];
                });
            }),
        ];
    }
}