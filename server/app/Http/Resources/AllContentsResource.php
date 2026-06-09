<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllContentsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'music_type' => $this->music_type,
            'artist' => $this->artist,
            'genre' => $this->genre,
            'release_date' => $this->release_date,
            'cover_image' => $this->cover_image_url,
            'cover_image_url' => $this->cover_image_url, 
            'label' => $this->label,
            'favorites_count' => $this->favorites_count,
            'reviews_count' => $this->reviews_count,
            'added_by' => $this->added_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}