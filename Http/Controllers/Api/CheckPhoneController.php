<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckPhoneController extends Controller
{
    /**
     * Check if a phone number exists in the system
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPhone(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string|max:20',
                'country_code' => 'required|string|max:5'
            ]);

            if ($validator->fails()) {
                return (new ApiResponse(
                    422,
                    __('messages.validation_error'),
                    ['errors' => $validator->errors()]
                ))->send();
            }

            // Find user by phone number
            $user = User::where('phone', $request->phone_number)
                ->where('country_code', $request->country_code)
                ->first();

            if ($user) {
                // Generate authentication token
                $token = $user->createToken('phone-check-token')->plainTextToken;

                return (new ApiResponse(
                    200,
                    __('messages.phone_number_found'),
                    [
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->full_name,
                            'phone' => $user->phone,
                            'country_code' => $user->country_code
                        ]
                    ]
                ))->send();
            }

            return (new ApiResponse(
                200,
                __('messages.phone_number_not_found'),
                ['token' => null]
            ))->send();

        } catch (\Exception $e) {
            Log::error('Phone check error: ' . $e->getMessage(), [
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
