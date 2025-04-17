<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthQuestionResource;
use App\Models\Section;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GetSectionQuestionsController extends Controller
{
    /**
     * Get questions for a specific section
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'id' => 'required|integer|exists:sections,id'
            ]);

            // Get the section with its questions
            $section = Section::with(['questions' => function ($query) {
                $query->orderBy('id', 'asc');
            }])->where('id', $request->id)->first();

            if (!$section) {
                return (new ApiResponse(
                    404,
                    __('messages.section_not_found'),
                    []
                ))->send();
            }

            // Return questions with proper resource transformation
            return (new ApiResponse(
                200,
                __('messages.questions_retrieved_successfully'),
                [
                    'questions' => HealthQuestionResource::collection($section->questions),
                    'section' => [
                        'id' => $section->id,
                        'name' => $section->name
                    ]
                ]
            ))->send();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return (new ApiResponse(
                422,
                __('messages.validation_error'),
                ['errors' => $e->errors()]
            ))->send();
        } catch (\Exception $e) {
            Log::error('Error retrieving section questions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                500,
                __('messages.server_error'),
                ['error' => $e->getMessage()]
            ))->send();
        }
    }
}
