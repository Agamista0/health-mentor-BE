<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'gender' => $this->gender,
            'age' => $this->age,
            'address' => $this->address,
            'health_status' => $this->health_status,
            'description_disease' => $this->description_disease,
            'is_current' => $this->is_current,
            'avatar' => new AvatarResource($this->whenLoaded('avatar')),
            'body_status' => new BodyStatusResource($this->whenLoaded('bodyStatus')),
            'subscription' => new SubscriptionUserResource($this->whenLoaded('subscriptionUser')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
