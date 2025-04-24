<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Response\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VerifyOTPController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'email' => 'required_without:phone|email',
                'phone' => 'required_without:email|string|max:20',
                'otp' => 'required|string|size:6',
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
                    $user = User::where('email', $request->email)
                                ->where('otp', $request->otp)
                                ->first();
                } else {
                    $user = User::where('phone', $request->phone)
                                ->where('otp', $request->otp)
                                ->first();
                }

                if (!$user) {
                    DB::rollBack();
                    return (new ApiResponse(
                        400,
                        __('messages.invalid_otp'),
                        ['message' => 'Invalid OTP or user not found.']
                    ))->send();
                }

                // Check if OTP is expired (10 minutes)
                $otpCreatedAt = $user->updated_at;
                if (now()->diffInMinutes($otpCreatedAt) > 10) {
                    DB::rollBack();
                    return (new ApiResponse(
                        400,
                        __('messages.otp_expired'),
                        ['message' => 'OTP has expired. Please request a new one.']
                    ))->send();
                }

                // Update verification status
                if ($request->filled('email')) {
                    $user->email_verified_at = now();
                } else {
                    $user->phone_verified_at = now();
                }
                
                // Clear the OTP after successful verification
                $user->otp = null;
                $user->save();

                // Generate API token
                $token = $user->createToken('auth_token')->plainTextToken;

                DB::commit();

                return (new ApiResponse(
                    200,
                    __('messages.verification_successful'),
                    [
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->full_name,
                            'email' => $user->email,
                            'phone' => $user->phone,
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

