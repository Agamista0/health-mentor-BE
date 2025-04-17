<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BodyStatusResource;
use App\Models\BodyStatus;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetBodyStatusController extends Controller
{
    public function index()
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return (new ApiResponse(401, __('api.Unauthorized'), []))->send();
            }

            $userId = Auth::user()->id;
            
            // Get body status with proper error handling
            $bodyStatus = BodyStatus::where('user_id', $userId)->get();

            if ($bodyStatus->isEmpty()) {
                return (new ApiResponse(404, __('api.NoBodyStatusFound'), []))->send();
            }

            return (new ApiResponse(200, __('api.BodyStatusRetrievedSuccessfully'), [
                'health' => BodyStatusResource::collection($bodyStatus)
            ]))->send();

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in GetBodyStatusController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(500, __('api.ServerError'), []))->send();
        }
    }
}
