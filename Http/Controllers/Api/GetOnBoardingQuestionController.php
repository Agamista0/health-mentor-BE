<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthQuestionResource;
use App\Models\Section;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GetOnBoardingQuestionController extends Controller
{
    /**
     * Get onboarding questions for new users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Get the onboarding section with its questions
            $section = Section::with(['questions' => function ($query) {
                $query->orderBy('id', 'asc');
            }])->where('name', 'like', '%On Boarding%')->first();

            if (!$section) {
                return (new ApiResponse(
                    404,
                    __('messages.onboarding_section_not_found'),
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

        } catch (\Exception $e) {
            Log::error('Error retrieving onboarding questions: ' . $e->getMessage(), [
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
