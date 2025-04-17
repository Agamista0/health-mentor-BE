<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeleteFileController extends Controller
{
    public function index($id){
        try {    
            $file = File::find($id);
            if(isset($file)){
                $file->delete();
                return (new ApiResponse(200, __('api.FileDeletedSuccessfully'), []))->send();
            }else{
                return (new ApiResponse(404, __('api.FileNotFound'), []))->send();
            }

        } catch (\Exception $e) {
            Log::error('Error creating Note: ' . $e->getMessage());
    
            return (new ApiResponse(500, __('api.ServerError'),[]))->send();
        }
    }
}
