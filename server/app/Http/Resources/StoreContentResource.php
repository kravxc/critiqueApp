<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreContentResource extends JsonResource
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
            'cover_image'=> $this->cover_image,
            'label' => $this->label,
            'added_by' => $this->added_by
        ];
    }
}
