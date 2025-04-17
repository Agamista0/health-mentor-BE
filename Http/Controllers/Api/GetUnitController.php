<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetUnitController extends Controller
{
    public function index(Request $request){
        try {
            $type = $request->input('type');
            $units = Unit::where('type', $type)->get();
    
            return (new ApiResponse(200, __('UnitsRetrievedSuccessfully'), [
                'topic' => UnitResource::collection($units)
            ]))->send();
        } catch (\Exception $e) {
            Log::error('Error retrieving examinations: ' . $e->getMessage());
    
            return (new ApiResponse(500, __('api.ServerError'),[]))->send();
        }
    }
}
