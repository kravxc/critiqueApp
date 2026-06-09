<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopularReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->text,
            'likes_count' => $this->likes_count,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name ?? null,
                'avatar' => $this->user->avatar ?? null,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
