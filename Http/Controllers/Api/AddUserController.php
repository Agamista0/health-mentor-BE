<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use App\Models\User;
use App\Models\AnswerUser;
use App\Models\Section;
use App\Models\BodyStatus;
use App\Models\BodyStatusDetail;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AddUserController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate request data with more specific rules
            $validator = Validator::make($request->all(), [
                'username' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'unique:users,username',
                    'regex:/^[a-zA-Z0-9_]+$/'
                ],
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                    'regex:/^[a-zA-Z\s]+$/'
                ],
                'age' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:120'
                ],
                'gender' => [
                    'required',
                    'integer',
                    'in:0,1'
                ],
                'skin' => [
                    'required',
                    'exists:avatars,skin'
                ],
                'eye_color' => [
                    'required',
                    'exists:avatars,eye_color'
                ],
                'hair_style' => [
                    'required',
                    'exists:avatars,hair_style'
                ],
                'hair_color' => [
                    'required',
                    'exists:avatars,hair_color'
                ],
                'account_questions' => [
                    'sometimes',
                    'array'
                ],
                'account_questions.*.id' => [
                    'required',
                    'integer',
                    'exists:questions,id'
                ],
                'account_questions.*.answers' => [
                    'required',
                    'array',
                    'min:1'
                ],
                'account_questions.*.unit_id' => [
                    'nullable',
                    'integer',
                    'exists:units,id'
                ]
            ], [
                'username.regex' => 'Username can only contain letters, numbers, and underscores',
                'name.regex' => 'Name can only contain letters and spaces',
                'account_questions.*.answers.min' => 'At least one answer must be provided for each question'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Check if user already has maximum number of accounts
                $existingAccounts = User::where('user_id', auth()->user()->id)->count();
                if ($existingAccounts >= 5) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Maximum number of accounts (5) reached'
                    ], 400);
                }

                // Create user
                $user = User::create([
                    'user_id' => auth()->user()->id,
                    'username' => $request->username,
                    'age' => $request->age,
                    'gender' => $request->gender,
                    'full_name' => $request->name,
                ]);

                // Create avatar
                $avatar = $user->avatar()->create([
                    'skin' => $request->skin,
                    'eye_color' => $request->eye_color,
                    'hair_style' => $request->hair_style,
                    'hair_color' => $request->hair_color,
                ]);

                // Handle account questions if provided
                if ($request->has('account_questions')) {
                    $answers = [];
                    foreach ($request->account_questions as $question) {
                        foreach ($question['answers'] as $answer) {
                            $answerData = [
                                'user_id' => $user->id,
                                'question_id' => $question['id'],
                            ];

                            if (isset($question['unit_id'])) {
                                $answerData['value'] = $answer;
                                $answerData['unit_id'] = $question['unit_id'];
                            } else {
                                $answerData['answer_id'] = $answer;
                            }

                            $answers[] = $answerData;
                        }
                    }
                    AnswerUser::insert($answers);
                }

                // Create body status and details
                $sections = Section::where('name', '!=', 'On Boarding')->get();
                $bodyStatus = BodyStatus::create([
                    'user_id' => $user->id,
                    'status_mode' => 'empty',
                    'status_note' => null,
                ]);

                $bodyStatusDetails = $sections->map(function ($section) use ($bodyStatus) {
                    return [
                        'section_id' => $section->id,
                        'body_status_id' => $bodyStatus->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                })->toArray();

                BodyStatusDetail::insert($bodyStatusDetails);

                // Commit transaction
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'User created successfully',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'name' => $user->full_name,
                            'age' => $user->age,
                            'gender' => $user->gender,
                        ],
                        'avatar' => [
                            'skin' => $avatar->skin,
                            'eye_color' => $avatar->eye_color,
                            'hair_style' => $avatar->hair_style,
                            'hair_color' => $avatar->hair_color,
                        ],
                        'body_status' => [
                            'id' => $bodyStatus->id,
                            'status_mode' => $bodyStatus->status_mode,
                            'details_count' => count($bodyStatusDetails)
                        ]
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('User creation failed: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('User creation error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to create user',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }
}
