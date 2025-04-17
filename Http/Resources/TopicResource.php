<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->hasMedia('images') ? $this->getFirstMediaUrl('images') : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $imageUrl,
            'summary' => $this->summary,
            'free_content' => $this->free_content,
            'premium_content' => $this->premium_content,
            'doctor' => [
                'name' => $this->Doctor->name,
                'title' => $this->Doctor->title,
                'speciality' => $this->Doctor->speciality,
            ],
            'premium' => ($this->premium)?true:false,
        ];
    }
}
