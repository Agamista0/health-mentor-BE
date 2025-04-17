<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BodyStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status_mode' => $this->status_mode,
            'status_note' => $this->status_note,
            'details' => BodyStatusDetailResource::collection($this->Details)
        ];
    }
}
