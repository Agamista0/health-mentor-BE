<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Section;
use App\Models\AnswerUser;
use App\Models\BodyStatus;
use Illuminate\Http\Request;
use App\Models\BodyStatusDetail;
use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Response\ApiResponse;

class VerifyPhoneController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle registration request with Firebase verification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'gender' => 'required|integer|in:0,1',
                'email' => 'nullable|email|unique:users,email',
                'country_code' => 'required_without:email|string|max:5',
                'phone' => 'required_without:email|string|max:20|unique:users,phone',
                'age' => 'required|integer|min:1|max:120',
                'skin' => 'required|integer|in:0,1,2',
                'eye_color' => 'required|integer|in:0,1',
                'hair_style' => 'required|integer|in:0,1,2,3',
                'hair_color' => 'required|integer|in:0,1,2,3',
                'account_questions' => 'nullable|array',
                'account_questions.*.id' => 'required|exists:questions,id',
                'account_questions.*.answers' => 'required|array',
                'account_questions.*.unit_id' => 'nullable|exists:units,id'
            ]);

            if ($validator->fails()) {
                return (new ApiResponse(
                    422,
                    __('messages.validation_error'),
                    ['errors' => $validator->errors()]
                ))->send();
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Initiate Firebase verification first
                $verificationResult = null;
                if ($request->filled('email')) {
                    $verificationResult = $this->firebaseService->startEmailVerification($request->email, $request->name);
                } else {
                    $verificationResult = $this->firebaseService->startPhoneVerification($request->phone, $request->country_code);
                }

                if (!$verificationResult['success']) {
                    DB::rollBack();
                    return (new ApiResponse(
                        400,
                        __('messages.verification_failed'),
                        ['error' => $verificationResult['error']]
                    ))->send();
                }

                // Prepare user data
                $userData = [
                    'full_name' => $request->name,
                    'gender' => $request->gender,
                    'age' => $request->age,
                    'firebase_uid' => $verificationResult['uid'] ?? null
                ];

                // Add either email or phone details
                if ($request->filled('email')) {
                    $userData['email'] = $request->email;
                } else {
                    $userData['country_code'] = $request->country_code;
                    $userData['phone'] = $request->phone;
                    $userData['firebase_session'] = $verificationResult['sessionInfo'] ?? null;
                }

                // Create user
                $user = User::create($userData);

                // Create avatar
                $user->avatar()->create([
                    'skin' => $request->skin,
                    'eye_color' => $request->eye_color,
                    'hair_style' => $request->hair_style,
                    'hair_color' => $request->hair_color,
                ]);

                // Handle account questions
                if (isset($request->account_questions)) {
                    foreach ($request->account_questions as $question) {
                        foreach ($question['answers'] as $answer) {
                            $dataToInsert = [
                                'user_id' => $user->id,
                                'question_id' => $question['id'],
                            ];

                            if (isset($question['unit_id'])) {
                                $dataToInsert['value'] = $answer;
                                $dataToInsert['unit_id'] = $question['unit_id'];
                            } else {
                                $dataToInsert['answer_id'] = $answer;
                            }

                            AnswerUser::create($dataToInsert);
                        }
                    }
                }

                // Create body status
                $sections = Section::where('name', '!=', 'On Boarding')->get();
                $body_status = BodyStatus::create([
                    'user_id' => $user->id,
                    'status_mode' => 'empty',
                    'status_note' => null,
                ]);

                foreach ($sections as $section) {
                    BodyStatusDetail::create([
                        'section_id' => $section->id,
                        'body_status_id' => $body_status->id,
                    ]);
                }

                DB::commit();

                return (new ApiResponse(
                    200,
                    __('messages.registration_verification_sent'),
                    [
                        'verification_type' => $verificationResult['verification_type'],
                        'session_info' => $verificationResult['sessionInfo'] ?? null,
                        'otp' => $verificationResult['otp'] ?? null, // Remove in production
                        'user_id' => $user->id
                    ]
                ))->send();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                500,
                __('messages.server_error'),
                ['error' => 'Registration failed. Please try again.']
            ))->send();
        }
    }
}
