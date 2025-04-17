<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileDetailResource;
use App\Models\File;
use App\Models\FileDetail;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetFileDetailsController extends Controller
{
    public function index(Request $request){
        $request->validate([
            'file_id' => 'required|exists:files,id',
        ]);

        $file = File::with('Details')->find($request->file_id);

        return (new ApiResponse(200, __('api.FilesRetrievedSuccessfully'), [
            'details' => new FileDetailResource($file)
        ]))->send();

    }
}
