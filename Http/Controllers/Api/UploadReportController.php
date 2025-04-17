<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UploadReportController extends Controller
{
    
    public function index(Request $request){
        
        $validateUser = Validator::make($request->all(), [
            'image' => 'required_without:file',
            'file' => 'required_without:image'
        ]);


        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $report = hash('crc32b', Str::random(11));
        $report = intval($report, 16);
        $user = auth()->user();
        if ($request->hasFile('image')) {
        $images = $request->file('image');
        foreach ($images as $image) {
            $media = $user->addMedia($image)->toMediaCollection('images');
            $media->update([
                'report' => $report
            ]);
        }
        
    //  if ($request->hasFile('images')) {
    //     foreach ($request->file('images') as $image) {
    //         $user->addMedia($image)->toMediaCollection('images');
    //     }
    } else if ($request->hasFile('file')) {
          $image= $request->hasFile('images');
            $user->addMediaFromRequest('file')->toMediaCollection('files');
    }
    
    return response()->json([
        'status' => true,
        'message' => 'Report Uploaded Successfully',
    ], 200); 
    }
    
}