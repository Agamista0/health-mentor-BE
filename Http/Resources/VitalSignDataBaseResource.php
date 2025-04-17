<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VitalSignDataBaseResource extends JsonResource
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
            'title' => $this->name,
            'unit' => new UnitResource($this->whenLoaded('units')),

        ];
    }
}


class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_unit' => $this->parent_unit,
            'sub_units' => SubUnitResource::collection($this->whenLoaded('subUnits')),
        ];
    }
}

class SubUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_unit' => $this->sub_unit,
            'convert_unit' => $this->convert_unit,
        ];
    }
}