<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Handle user login request and generate OTP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|max:20',
                'country_code' => 'required|string|max:5'
            ]);

            if ($validator->fails()) {
                return (new ApiResponse(
                    422,
                    __('messages.validation_error'),
                    ['errors' => $validator->errors()]
                ))->send();
            }

            // Find user by phone and country code
            $user = User::where('country_code', $request->country_code)
                ->where('phone', $request->phone)
                ->first();

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

            // Generate authentication token
            $token = $user->createToken('AuthToken')->plainTextToken;

            return (new ApiResponse(
                200,
                __('messages.otp_created_successfully'),
                [
                    'otp' => $otp,
                    'token' => $token
                ]
            ))->send();

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
}
