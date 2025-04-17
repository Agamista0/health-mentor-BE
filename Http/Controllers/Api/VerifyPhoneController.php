<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Section;
use App\Models\AnswerUser;
use App\Models\BodyStatus;
use Illuminate\Http\Request;
use App\Models\BodyStatusDetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Response\ApiResponse;

class VerifyPhoneController extends Controller
{
    /**
     * Handle phone verification request
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
                'country_code' => 'required|string|max:5',
                'phone' => 'required|string|max:20',
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
            $existingUser = User::where('phone', $request->phone)->first();
            if ($existingUser) {
                return (new ApiResponse(
                    409,
                    __('messages.account_already_exists'),
                    []
                ))->send();
            }

            // Generate OTP
            $otp = rand(1000, 9999);

            // Prepare user data
            $newUserData = [
                'full_name' => $request->name,
                'gender' => $request->gender,
                'country_code' => $request->country_code,
                'phone' => $request->phone,
                'otp' => $otp,
                'age' => $request->age,
            ];

            // Prepare avatar data
            $avatarData = [
                'skin' => $request->skin,
                'eye_color' => $request->eye_color,
                'hair_style' => $request->hair_style,
                'hair_color' => $request->hair_color,
            ];

            // Store data in cache for OTP verification
            Cache::put('newUserData', $newUserData, now()->addMinutes(10));
            Cache::put('avatarData', $avatarData, now()->addMinutes(10));
            Cache::put('account_questions', $request->account_questions, now()->addMinutes(10));

            return (new ApiResponse(
                200,
                __('messages.otp_sent_successfully'),
                ['otp' => $otp]
            ))->send();

        } catch (\Exception $e) {
            Log::error('Phone verification error: ' . $e->getMessage(), [
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
