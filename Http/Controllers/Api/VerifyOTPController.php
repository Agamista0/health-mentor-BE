<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Section;
use App\Models\AnswerUser;
use App\Models\BodyStatus;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Models\BodyStatusDetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class VerifyOTPController extends Controller
{
    /**
     * Verify OTP and handle user authentication/registration
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
                'country_code' => 'required|string|max:5',
                'otp' => 'required|string|size:4'
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
                // Check if user exists with the given OTP and phone
                $user = User::where('otp', $request->otp)
                    ->where('phone', $request->phone)
                    ->where('country_code', $request->country_code)
                    ->first();

                if ($user) {
                    // Generate authentication token
                    $token = $user->createToken('API Token')->plainTextToken;

                    DB::commit();

                    return (new ApiResponse(
                        200,
                        __('messages.user_logged_in_successfully'),
                        ['token' => $token]
                    ))->send();
                }

                // If user doesn't exist, create new user from cached data
                $newUserData = Cache::get('newUserData');
                $avatarData = Cache::get('avatarData');
                $account_questions = Cache::get('account_questions');

                if (!$newUserData || !$avatarData) {
                    DB::rollBack();
                    return (new ApiResponse(
                        400,
                        __('messages.invalid_or_expired_otp'),
                        []
                    ))->send();
                }

                // Create new user
                $user = User::create($newUserData);
                $user->avatar()->create($avatarData);

                // Handle account questions if provided
                if (isset($account_questions)) {
                    foreach ($account_questions as $question) {
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

                // Clear cache
                Cache::forget('newUserData');
                Cache::forget('avatarData');
                Cache::forget('account_questions');

                // Generate token for new user
                $token = $user->createToken('API Token')->plainTextToken;

                DB::commit();

                return (new ApiResponse(
                    201,
                    __('messages.user_created_successfully'),
                    ['token' => $token]
                ))->send();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('User creation/verification failed: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('OTP verification error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                500,
                __('messages.server_error'),
                ['error' => $e->getMessage()]
            ))->send();
        }
    }

    public function valid($requestData)
    {
        $validator = Validator::make($requestData, [
            'otp' => 'required',
        ]);

        return $validator;
    }
}

