<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExaminationDetailResource;
use App\Models\MedicalTest;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetExaminationDetailController extends Controller
{
    public function index(Request $request){
        try {
            $details = MedicalTest::with('values')->find($request->input('id'));
    
            return (new ApiResponse(200, __('api.ExaminationsRetrievedSuccessfully'), [
                'details' => new ExaminationDetailResource($details)
            ]))->send();
        } catch (\Exception $e) {
            Log::error('Error retrieving examinations: ' . $e->getMessage());
    
            return (new ApiResponse(500, __('api.ServerError'),[]))->send();
        }
    }
}
