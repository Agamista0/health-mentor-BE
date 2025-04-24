<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Response\ApiResponse;
use App\Services\FirebaseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VerifyOTPController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Verify OTP and authenticate user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'email' => 'required_without_all:phone,country_code|email',
                'phone' => 'required_without:email|string|max:20',
                'country_code' => 'required_with:phone|string|max:5',
                'otp' => 'required|string|size:6',
                'session_info' => 'required_with:phone'
            ]);

            if ($validator->fails()) {
                return (new ApiResponse(
                    422,
                    __('messages.validation_error'),
                    ['errors' => $validator->errors()]
                ))->send();
            }

            DB::beginTransaction();

            try {
                // Find user
                $user = null;
                if ($request->filled('email')) {
                    $user = User::where('email', $request->email)->first();
                } else {
                    $user = User::where('phone', $request->phone)
                            ->where('country_code', $request->country_code)
                            ->first();
                }

                if (!$user) {
                    DB::rollBack();
                    return (new ApiResponse(
                        404,
                        __('messages.user_not_found'),
                        ['message' => 'User not found.']
                    ))->send();
                }

                // Verify with Firebase
                $verificationResult = null;
                if ($request->filled('email')) {
                    $verificationResult = $this->firebaseService->verifyEmailOTP($request->email, $request->otp);
                } else {
                    $verificationResult = $this->firebaseService->verifyPhoneOTP($request->session_info, $request->otp);
                }

                if (!$verificationResult['success']) {
                    DB::rollBack();
                    return (new ApiResponse(
                        400,
                        __('messages.invalid_otp'),
                        ['error' => $verificationResult['error']]
                    ))->send();
                }

                // Update verification status and Firebase UID
                if ($request->filled('email')) {
                    $user->email_verified_at = now();
                } else {
                    $user->phone_verified_at = now();
                }
                
                $user->firebase_uid = $verificationResult['uid'];
                $user->save();

                DB::commit();

                return (new ApiResponse(
                    200,
                    __('messages.verification_successful'),
                    [
                        'token' => $verificationResult['token'],
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->full_name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'country_code' => $user->country_code,
                            'email_verified' => !is_null($user->email_verified_at),
                            'phone_verified' => !is_null($user->phone_verified_at)
                        ]
                    ]
                ))->send();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Verification error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                500,
                __('messages.server_error'),
                ['error' => 'Verification failed.']
            ))->send();
        }
    }
}

