<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait FileUrlTrait
{
    /**
     * Get a relative URL for a file path
     *
     * @param string $path
     * @return string
     */
    protected function getRelativeFileUrl(string $path): string
    {
        $url = Storage::url($path);
        
        // Ensure the path starts with a slash
        if (substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        
        return $url;
    }
} 