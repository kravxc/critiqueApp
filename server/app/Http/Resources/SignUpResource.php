<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class SignUpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'token' => $this->resource['token'],
                'user' => $this->resource['user'],
            ]
        ];
    }
    public function withResponse($request, $response): void
    {
        $response->setStatusCode(201);
    }
}
