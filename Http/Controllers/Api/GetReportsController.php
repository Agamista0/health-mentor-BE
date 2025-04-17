<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReportResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;


class GetReportsController extends Controller
{
     public function index(){
        try {
            $user = auth()->user();
            
            $imagesMediaItems = $user->getMedia('images');
            $filesMediaItems = $user->getMedia('files');
            $groupedMedia = [];
            
            $hasNonNullReports = $imagesMediaItems->contains('report', '!=', null);

            if ($hasNonNullReports) {
                $groupedMedia = $imagesMediaItems->filter(function ($media) {
                    return $media->report !== null;
                })->groupBy('report')->map(function ($mediaItems) {
                    return [ 'report' => $mediaItems->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'image_path' => $media->getUrl(),
                            'key' => ($media->collection_name == 'files') ? 1 : 0,
                            'created_at' => $media->created_at,
                        ];
                    })
                    ];
                });
            }
          
            
            $data = $imagesMediaItems->filter(function ($media) {
                 return $media->report === null;
                })->concat($filesMediaItems)->map(function ($media) {
                    $arr['report'] = array([
                        'id' => $media->id,
                        'image_path' => $media->getUrl(),
                        'key' => ($media->collection_name == 'files') ? 1 : 0,
                        'created_at' => $media->created_at,
                    ]);
                return $arr;
            });
           

            $mergedData = $data->merge($groupedMedia);


            return (new ApiResponse(200, __('Reports Retrieved Successfully'), [
                'reports' => $mergedData,
                'user' => $user->makeHidden('media'),
            ]))->send(); 

        } catch (\Exception $e) {
            Log::error('Error retrieving reports: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(500, __('Server Error'), []))->send();
        }
    }
    
}