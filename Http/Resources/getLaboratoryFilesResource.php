<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class getLaboratoryFilesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
      // Check the file type
        if ($this->file_type === 'image') {
            // Generate URL for image from storage
            $fileUrl = config('app.url') . Storage::url($this->file_path);
        } elseif ($this->file_type === 'pdf') {
            // Generate the PDF viewer URL using the route helper function
            //$fileUrl = route('pdf-viewer', ['filename' => basename($this->file_path)]);
			$fileUrl = config('app.url') . Storage::url($this->file_path);

        } else {
            // Return the original file path for other file types
            $fileUrl = $this->file_path;
        }
        return [
            'id' => $this->id,
            'file_type' => $this->file_type,
            'file_path' => $fileUrl,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include any other attributes you want to expose
        ];
    }
}
