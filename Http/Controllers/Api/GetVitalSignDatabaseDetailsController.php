<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\getVitalSignDatabaseDetailsResource;
use App\Models\MedicalTest;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetVitalSignDatabaseDetailsController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Check if id is provided
            if (!$request->has('id')) {
                return (new ApiResponse(400, __('api.VitalSignIdRequired'), []))->send();
            }

            // Validate id is integer
            if (!is_numeric($request->input('id'))) {
                return (new ApiResponse(400, __('api.VitalSignIdMustBeInteger'), []))->send();
            }

            $details = MedicalTest::with('wiki')
                ->where('id', $request->input('id'))
                ->first();

            if (!$details) {
                return (new ApiResponse(404, __('api.DetailsNotFound'), []))->send();
            }

            $data = [];
            
            // Check if wiki entries exist
            if ($details->wiki->isNotEmpty()) {
                foreach (getVitalSignDatabaseDetailsResource::collection($details->wiki) as $resource) {
                    $data[] = $resource->toArray($request);
                }
            }

            // Add the medical test details at the beginning
            array_unshift($data, [
                'title' => $details->name,
                'about' => $details->description
            ]);

            return (new ApiResponse(200, __('api.DetailsRetrievedSuccessfully'), $data))->send();

        } catch (\Exception $e) {
            Log::error('Error in GetVitalSignDatabaseDetailsController: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(500, __('api.ServerError'), []))->send();
        }
    }
}
