<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LastUserReviewsResource extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($review) {
                return [
                    'content' => [
                        'title' => $review->content->title,
                        'artist' => $review->content->artist,
                    ],
                    'review' => [
                        'id' => $review->id,
                        'title' => $review->title,
                        'text' => $review->text,
                        'likes_count' => $review->likes->count(),
                        'reviews_likes' => $review->review_likes,
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
                    ],
                ];
            }),
        ];
    }
}
