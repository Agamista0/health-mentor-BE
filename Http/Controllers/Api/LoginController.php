<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Response\ApiResponse;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle login request and generate OTP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            Log::info('Login attempt', $request->all());

            // Validate request data
            $validator = Validator::make($request->all(), [
                'email' => 'required_without_all:phone,country_code|email',
                'phone' => 'required_without:email|string|max:20',
                'country_code' => 'required_with:phone|string|max:5'
            ]);

            if ($validator->fails()) {
                Log::warning('Login validation failed', ['errors' => $validator->errors()]);
                return (new ApiResponse(
                    422,
                    __('messages.validation_error'),
                    ['errors' => $validator->errors()]
                ))->send();
            }

            // Check if user exists
            $user = null;
            if ($request->filled('email')) {
                $user = User::where('email', $request->email)->first();
            } else {
                $user = User::where('phone', $request->phone)
                    ->where('country_code', $request->country_code)
                    ->first();
            }

            if (!$user) {
                Log::info('User not found', [
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'country_code' => $request->country_code
                ]);
                return (new ApiResponse(
                    404,
                    __('messages.user_not_found'),
                    ['message' => 'Please register first.']
                ))->send();
            }

            // Send verification code via Firebase
            $verificationResult = null;
            if ($request->filled('email')) {
                $verificationResult = $this->firebaseService->sendEmailOTP($request->email);
            } else {
                $verificationResult = $this->firebaseService->sendPhoneOTP($request->phone, $request->country_code);
            }

            if (!$verificationResult['success']) {
                Log::error('Firebase verification failed', [
                    'error' => $verificationResult['error'],
                    'user_id' => $user->id
                ]);
                return (new ApiResponse(
                    400,
                    __('messages.verification_failed'),
                    ['error' => $verificationResult['error']]
                ))->send();
            }

            // Store Firebase session info for phone verification
            if (isset($verificationResult['sessionInfo'])) {
                $user->update(['firebase_session' => $verificationResult['sessionInfo']]);
            }

            Log::info('OTP sent successfully', [
                'user_id' => $user->id,
                'verification_type' => $verificationResult['verification_type']
            ]);

            return (new ApiResponse(
                200,
                __('messages.otp_sent'),
                [
                    'verification_type' => $verificationResult['verification_type'],
                    'session_info' => $verificationResult['sessionInfo'] ?? null,
                    'user_id' => $user->id
                ]
            ))->send();

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return (new ApiResponse(
                500,
                __('messages.server_error'),
                ['error' => 'Failed to send verification code. Please try again later.']
            ))->send();
        }
    }
}
