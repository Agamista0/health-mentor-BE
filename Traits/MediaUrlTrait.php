<?php

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait MediaUrlTrait
{
    /**
     * Get the relative path for a media item without the domain
     *
     * @param string $collectionName
     * @return string|null
     */
    public function getRelativeMediaUrl($collectionName)
    {
        if (!$this->hasMedia($collectionName)) {
            return null;
        }

        $media = $this->getFirstMedia($collectionName);
        
        if (!$media) {
            return null;
        }

        // Get the path relative to the storage root
        $path = $media->getPathRelativeToRoot();
        
        // Ensure the path starts with a slash
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        
        return $path;
    }
} 