<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AleafiaAssessment;
use App\Models\AleafiaQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AleafiaAssessmentController extends Controller
{

    public function getQuestions()
    {
        try {
            $questions = AleafiaQuestion::all();

            if ($questions->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No questions found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Questions retrieved successfully',
                'data' => $questions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitAssessment(Request $request)
    {
        try {
            // Validate request
            $validator = $this->validateAssessment($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get all questions
            $questions = AleafiaQuestion::all();
            if ($questions->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No questions found in the system'
                ], 404);
            }

            // Process answers and calculate scores
            $result = $this->processAnswers($request->answers, $questions);
            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message']
                ], 400);
            }

            // Save assessment
            $assessment = AleafiaAssessment::create([
                'user_id' => Auth::id(),
                'total_score' => $result['data']['total_score'],
                'category_scores' => $result['data']['category_scores'],
                'answers' => $result['data']['processed_answers']
            ]);

            // Format response for the UI
            $formattedResponse = $this->formatResponseForUI($result['data']);

            return response()->json([
                'status' => true,
                'message' => 'Assessment submitted successfully',
                'data' => $formattedResponse
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error submitting assessment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function validateAssessment(Request $request)
    {
        return Validator::make($request->all(), [
            'answers' => 'required|array|min:6',
            'answers.*.question_id' => 'required|exists:aleafia_questions,id',
            'answers.*.answer_option_id' => 'required|integer|min:0|max:3'
        ]);
    }

    private function processAnswers(array $answers, $questions)
    {
        try {
            $categoryScores = [];
            $totalScore = 0;
            $processedAnswers = [];
            $totalPossibleScore = 0;

            foreach ($answers as $answer) {
                $question = $questions->firstWhere('id', $answer['question_id']);
                if (!$question) {
                    return [
                        'status' => false,
                        'message' => "Question with ID {$answer['question_id']} not found"
                    ];
                }

                $selectedOption = collect($question->answer_options)
                    ->firstWhere('id', $answer['answer_option_id']);
                
                if (!$selectedOption) {
                    return [
                        'status' => false,
                        'message' => "Invalid answer option ID for question {$answer['question_id']}"
                    ];
                }

                if (!isset($categoryScores[$question->category])) {
                    $categoryScores[$question->category] = [
                        'total' => 0,
                        'max_possible' => 0,
                        'count' => 0
                    ];
                }

                $categoryScores[$question->category]['total'] += $selectedOption['score'];
                $categoryScores[$question->category]['max_possible'] += 100; // Max score per question
                $categoryScores[$question->category]['count']++;
                
                $totalScore += $selectedOption['score'];
                $totalPossibleScore += 100;

                $processedAnswers[] = [
                    'question_id' => $answer['question_id'],
                    'category' => $question->category,
                    'answer_option_id' => $answer['answer_option_id'],
                    'answer_text' => $selectedOption['text'],
                    'score' => $selectedOption['score']
                ];
            }

            // Calculate percentages for each category
            $finalCategoryScores = [];
            foreach ($categoryScores as $category => $data) {
                $percentage = ($data['total'] / $data['max_possible']) * 100;
                $finalCategoryScores[$category] = [
                    'score' => $data['total'] / $data['count'],
                    'percentage' => round($percentage)
                ];
            }

            return [
                'status' => true,
                'data' => [
                    'total_score' => ($totalScore / $totalPossibleScore) * 100,
                    'category_scores' => $finalCategoryScores,
                    'processed_answers' => $processedAnswers
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error processing answers: ' . $e->getMessage()
            ];
        }
    }

    private function formatResponseForUI($data)
    {
        // Dynamically group categories by type from the processed answers
        $typeCategories = [];
        foreach ($data['processed_answers'] as $answer) {
            $category = $answer['category'];
            $question = AleafiaQuestion::where('category', $category)->first();
            if ($question) {
                $type = $question->type;
                if (!isset($typeCategories[$type])) {
                    $typeCategories[$type] = [];
                }
                if (!in_array($category, $typeCategories[$type])) {
                    $typeCategories[$type][] = $category;
                }
            }
        }

        // Calculate scores and percentages by type
        $typeScores = [];
        foreach ($typeCategories as $type => $categories) {
            $totalScore = 0;
            $categoryCount = 0;
            
            foreach ($categories as $category) {
                if (isset($data['category_scores'][$category])) {
                    $totalScore += $data['category_scores'][$category]['percentage'] ?? 0;
                    $categoryCount++;
                }
            }
            
            $typeScores[$type] = $categoryCount > 0 ? round($totalScore / $categoryCount) : 0;
        }

        // Calculate pie chart data
        $pieChartData = [];
        $totalScore = 0;
        
        // First pass: calculate total score
        foreach ($data['category_scores'] as $category => $scores) {
            $totalScore += $scores['percentage'] ?? 0;
        }

        // Second pass: calculate normalized percentages
        foreach ($data['category_scores'] as $category => $scores) {
            $percentage = $scores['percentage'] ?? 0;
            // If total is 0, distribute evenly, otherwise calculate relative percentage
            $normalizedPercentage = $totalScore === 0 ? 
                (100 / count($data['category_scores'])) : 
                round(($percentage / $totalScore) * 100);
            
            $pieChartData[] = [
                'label' => $category,
                'percentage' => $normalizedPercentage,
            ];
        }

        // Build progress circles dynamically
        $progressCircles = [];
        foreach ($typeScores as $type => $score) {
            $progressCircles[$type] = [
                'score' => $score,
                'total' => 100,
            ];
        }

        return [
            'pie_chart' => [
                'data' => $pieChartData,
                'categories' => $pieChartData
            ],
            'progress_circles' => $progressCircles,
        ];
    }

    // Helper method to get category type
    private function getCategoryType($category)
    {
        $question = AleafiaQuestion::where('category', $category)->first();
        return $question ? $question->type : null;
    }

    // Helper method to get type label
    private function getTypeLabel($type)
    {
        $labels = [
            'mental' => 'نفسي',
            'physical' => 'جسدي'
        ];
        return $labels[$type] ?? $type;
    }

    public function getUserHistory()
    {
        try {
            $assessments = AleafiaAssessment::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            if ($assessments->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No assessment history found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Assessment history retrieved successfully',
                'data' => $assessments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving assessment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 