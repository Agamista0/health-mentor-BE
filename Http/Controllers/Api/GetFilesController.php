<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetFilesController extends Controller
{
    public function index(){
        try {
            $files = File::where('user_id', auth()->user()->id)->get();
    
            return (new ApiResponse(200, __('FilesRetrievedSuccessfully'), [
                'topic' => FileResource::collection($files)
            ]))->send();
        } catch (\Exception $e) {
            Log::error('Error retrieving examinations: ' . $e->getMessage());
    
            return (new ApiResponse(500, __('api.ServerError'),[]))->send();
        }
    }
}
