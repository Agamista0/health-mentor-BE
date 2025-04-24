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
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;

class VerifyPhoneController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle registration request
     */
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required_without:phone|email|max:255',
                'phone' => 'required_without:email|string|max:20',
                'country_code' => 'required_with:phone|string|max:5',
                'firebase_token' => 'required_with:phone|string',
                'gender' => 'required|integer|in:0,1',
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

            // Check if user already exists
            $query = User::query();
            
            if ($request->email) {
                $query->where('email', $request->email);
            } else {
                $query->where('phone', $request->phone)
                      ->where('country_code', $request->country_code);
            }

            $existingUser = $query->first();

            if ($existingUser) {
                return (new ApiResponse(
                    409,
                    __('messages.account_already_exists'),
                    []
                ))->send();
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Handle registration based on type
                if ($request->email) {
                    return $this->handleEmailRegistration($request);
                } else {
                    return $this->handlePhoneRegistration($request);
                }

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
                ['error' => $e->getMessage()]
            ))->send();
        }
    }

    /**
     * Handle email-based registration
     */
    protected function handleEmailRegistration(Request $request)
    {
        // Generate OTP
        $otp = rand(100000, 999999);

        // Create new user
        $user = User::create([
            'full_name' => $request->name,
            'gender' => $request->gender,
            'age' => $request->age,
            'email' => $request->email,
            'otp' => $otp
        ]);

        // Create avatar
        $user->avatar()->create([
            'skin' => $request->skin,
            'eye_color' => $request->eye_color,
            'hair_style' => $request->hair_style,
            'hair_color' => $request->hair_color,
        ]);

        // Handle account questions
        $this->handleAccountQuestions($request, $user);

        // Send verification email
        Mail::to($request->email)->send(new OtpVerificationMail($otp, $request->name));

        // Generate token
        $token = $user->createToken('API Token')->plainTextToken;

        DB::commit();

        return (new ApiResponse(
            200,
            __('messages.user_created_successfully'),
            [
                'token' => $token,
                'otp' => $otp,
                'auth_type' => 'email'
            ]
        ))->send();
    }

    /**
     * Handle phone-based registration with Firebase
     */
    protected function handlePhoneRegistration(Request $request)
    {
        try {
            // Verify Firebase token
            $firebaseToken = $this->firebaseService->verifyToken($request->firebase_token);
            
            if (!$firebaseToken) {
                return (new ApiResponse(
                    401,
                    __('messages.invalid_firebase_token'),
                    []
                ))->send();
            }

            // Create new user
            $user = User::create([
                'full_name' => $request->name,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'country_code' => $request->country_code,
                'firebase_uid' => $firebaseToken->claims()->get('sub')
            ]);

            // Create avatar
            $user->avatar()->create([
                'skin' => $request->skin,
                'eye_color' => $request->eye_color,
                'hair_style' => $request->hair_style,
                'hair_color' => $request->hair_color,
            ]);

            // Handle account questions
            $this->handleAccountQuestions($request, $user);

            // Generate token
            $token = $user->createToken('API Token')->plainTextToken;

            DB::commit();

            return (new ApiResponse(
                200,
                __('messages.user_created_successfully'),
                [
                    'token' => $token,
                    'auth_type' => 'phone',
                    'firebase_uid' => $firebaseToken->claims()->get('sub')
                ]
            ))->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Firebase registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                401,
                __('messages.firebase_auth_failed'),
                ['error' => $e->getMessage()]
            ))->send();
        }
    }

    /**
     * Handle account questions
     */
    protected function handleAccountQuestions(Request $request, User $user)
    {
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

        // Create body status and details
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
    }
}
