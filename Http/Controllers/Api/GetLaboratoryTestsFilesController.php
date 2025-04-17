<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\getLaboratoryTestsResource;
use App\Models\LaboratoryTests;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class GetLaboratoryTestsFilesController extends Controller
{
    /**
     * Get paginated laboratory test files for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Get all laboratory tests for the user with eager loading
            $laboratoryTests = LaboratoryTests::with('files')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform the data using the resource
            $laboratoryTestsResource = getLaboratoryTestsResource::collection($laboratoryTests);

            // Set up pagination parameters
            $perPage = $request->input('per_page', 10);
            $currentPage = $request->input('page', 1);

            // Create paginator manually
            $items = $laboratoryTestsResource->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $paginator = new LengthAwarePaginator(
                $items,
                $laboratoryTestsResource->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            // Prepare response data
            $responseData = [
                'reports' => $paginator->items(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem()
                ]
            ];

            return (new ApiResponse(
                200,
                __('messages.laboratory_tests_retrieved_successfully'),
                $responseData
            ))->send();

        } catch (\Exception $e) {
            Log::error('Error retrieving laboratory tests: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                500,
                __('messages.server_error'),
                []
            ))->send();
        }
    }
}
