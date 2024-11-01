<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'alt' => $this->alt,
            'title' => $this->title,
            'url' => $this->url,
            'thumbnail_url' => url($this->thumbnail_url),
            'medium_url' => url($this->medium_url),
            'large_url' => url($this->large_url),
            'caption' => $this->caption
        ];
    }
}
