<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getLaboratoryTestsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        
        return [
            'id' => $this->id,
            'description' => $this->description,
            // 'user' => new UserResource($this->user), // Assuming 'user' is the relationship method
            'created_at' => $this->created_at->toDateTimeString(),
            // Include any other attributes you want to expose
            'files' => getLaboratoryFilesResource::collection($this->whenLoaded('files')),
        ];
    }
}
