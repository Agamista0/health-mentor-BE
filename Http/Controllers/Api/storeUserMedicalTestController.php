<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\MedicalTest;
use App\Models\MedicalTestValue;
use App\Models\UserMedicalTest;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class storeUserMedicalTestController extends Controller
{
    /**
     * Store a new user medical test
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     * @throws \Exception
     */
    public function index(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'medical_test_id' => 'required|exists:medical_tests,id',
                'value' => 'required',
                'value2' => 'nullable',
                'date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Get the authenticated user
            $user = Auth::user();

            // Get medical test with units
            $medicalTest = MedicalTest::with('units')
                ->where('id', $request->medical_test_id)
                ->first();

            if (!$medicalTest) {
                return (new ApiResponse(
                    404,
                    __('messages.medical_test_not_found'),
                    []
                ))->send();
            }

            // Get medical test value if exists
            $medicalTestValue = MedicalTestValue::where('medical_test_id', $request->medical_test_id)
                ->first();

            // Create new user medical test
            $userMedicalTest = UserMedicalTest::create([
                'user_id' => $user->id,
                'medical_test_id' => $request->medical_test_id,
                'medical_test_value_id' => $medicalTestValue ? $medicalTestValue->id : null,
                'type' => $medicalTest->name,
                'value' => $request->value,
                'value2' => $request->value2,
                'date' => $request->date ?? date('Y-m-d'),
                'unit_id' => $medicalTest->units ? $medicalTest->units->id : null
            ]);

            // Prepare response data
            $responseData = [
                'medical_test' => [
                    'id' => $userMedicalTest->id,
                    'type' => $userMedicalTest->type,
                    'value' => $userMedicalTest->value,
                    'value2' => $userMedicalTest->value2,
                    'date' => $userMedicalTest->date,
                    'unit' => $medicalTest->units ? [
                        'id' => $medicalTest->units->id,
                        'name' => $medicalTest->units->name
                    ] : null,
                    'created_at' => $userMedicalTest->created_at
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ];

            return (new ApiResponse(
                201,
                __('messages.medical_test_created_successfully'),
                $responseData
            ))->send();

        } catch (ValidationException $e) {
            return (new ApiResponse(
                422,
                __('messages.validation_error'),
                ['errors' => $e->errors()]
            ))->send();
        } catch (\Exception $e) {
            Log::error('Error creating medical test: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(
                500,
                __('messages.server_error'),
                []
            ))->send();
        }
    }
}
