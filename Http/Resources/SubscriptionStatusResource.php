<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'free_trial' => $this->free_trial == 1,
            'create_date' => $this->create_date,
            'end_date' => $this->end_date,
            'subscription_ended' => $this->subscription_ended == 1,
            'subsctiption' => new SubscriptionResource($this->Subscription),
        ];
    }
}
