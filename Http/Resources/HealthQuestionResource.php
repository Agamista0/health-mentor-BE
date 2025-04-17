<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthQuestionResource extends JsonResource
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
            'question' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
           'units' => ($this->type == 2) ? $this->units->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'title' => $item['title']
                ];
            })->toArray() : [],

            'answers' => AnswerResource::collection($this->answers)
        ];
    }
}
