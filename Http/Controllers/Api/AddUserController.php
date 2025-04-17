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

class AddUserController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'username' => 'required|string|min:3|max:50|unique:users,username',
                'name' => 'required|string|min:2|max:100',
                'age' => 'required|integer|min:1|max:120',
                'gender' => 'required|integer|in:0,1',
                'skin' => 'required',
                'eye_color' => 'required',
                'hair_style' => 'required',
                'hair_color' => 'required',
                'account_questions' => 'sometimes|array',
                'account_questions.*.id' => 'required|integer|exists:questions,id',
                'account_questions.*.answers' => 'required|array',
                'account_questions.*.unit_id' => 'nullable|integer|exists:units,id'
            ]);

            // Start database transaction
            DB::beginTransaction();

            try {
                // Create user
                $user = User::create([
                    'user_id' => auth()->user()->id,
                    'username' => $validatedData['username'],
                    'age' => $validatedData['age'],
                    'gender' => $validatedData['gender'],
                    'full_name' => $validatedData['name'],
                ]);

                // Create avatar
                $avatarData = [
                    'skin' => $validatedData['skin'],
                    'eye_color' => $validatedData['eye_color'],
                    'hair_style' => $validatedData['hair_style'],
                    'hair_color' => $validatedData['hair_color'],
                ];
                $user->avatar()->create($avatarData);

                // Handle account questions if provided
                if (isset($validatedData['account_questions'])) {
                    foreach ($validatedData['account_questions'] as $question) {
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

                // Commit transaction
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'User created successfully',
                    'data' => [
                        'user' => $user,
                        'avatar' => $user->avatar,
                        'body_status' => $body_status
                    ]
                ], 201);

            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                Log::error('User creation failed: ' . $e->getMessage());
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
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
