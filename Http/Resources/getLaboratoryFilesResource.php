<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUrlTrait;

class getLaboratoryFilesResource extends JsonResource
{
    use FileUrlTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        if ($this->file_path) {
            return [
                'id' => $this->id,
                'file_path' => $this->getRelativeFileUrl($this->file_path),
                'file_type' => $this->file_type,
                'created_at' => $this->created_at,
            ];
        }
        return [
            'id' => $this->id,
            'file_path' => null,
            'file_type' => null,
            'created_at' => $this->created_at,
        ];
    }
}
