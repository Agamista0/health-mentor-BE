<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Response\ApiResponse;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle user login request
     */
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'email' => 'required_without:phone|email|max:255',
                'phone' => 'required_without:email|string|max:20',
                'country_code' => 'required_with:phone|string|max:5',
                'firebase_token' => 'required_with:phone|string'
            ]);

            if ($validator->fails()) {
                return (new ApiResponse(
                    422,
                    __('messages.validation_error'),
                    ['errors' => $validator->errors()]
                ))->send();
            }

            // Handle login based on type
            if ($request->email) {
                return $this->handleEmailLogin($request);
            } else {
                return $this->handlePhoneLogin($request);
            }

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
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
     * Handle email-based login
     */
    protected function handleEmailLogin(Request $request)
    {
        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return (new ApiResponse(
                404,
                __('messages.user_not_found'),
                []
            ))->send();
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Update user's OTP
        $user->update(['otp' => $otp]);

        // Send OTP email
        Mail::to($request->email)->send(new OtpVerificationMail($otp, $user->full_name));

        // Generate authentication token
        $token = $user->createToken('AuthToken')->plainTextToken;

        return (new ApiResponse(
            200,
            __('messages.otp_created_successfully'),
            [
                'otp' => $otp,
                'token' => $token,
                'auth_type' => 'email'
            ]
        ))->send();
    }

    /**
     * Handle phone-based login with Firebase
     */
    protected function handlePhoneLogin(Request $request)
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

            // Get or create user
            $user = User::where('phone', $request->phone)
                       ->where('country_code', $request->country_code)
                       ->first();

            if (!$user) {
                return (new ApiResponse(
                    404,
                    __('messages.user_not_found'),
                    []
                ))->send();
            }

            // Generate Laravel Sanctum token
            $token = $user->createToken('AuthToken')->plainTextToken;

            return (new ApiResponse(
                200,
                __('messages.login_successful'),
                [
                    'token' => $token,
                    'auth_type' => 'phone',
                    'firebase_uid' => $firebaseToken->claims()->get('sub')
                ]
            ))->send();

        } catch (\Exception $e) {
            Log::error('Firebase authentication error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                401,
                __('messages.firebase_auth_failed'),
                ['error' => $e->getMessage()]
            ))->send();
        }
    }
}
