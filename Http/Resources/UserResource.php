<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'avatar' => $this->avatar,
            'accounts' => UserResource::collection($this->accounts??[])
        ];
    }
}
