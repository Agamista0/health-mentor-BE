<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserMedicalTest;
use App\Models\MedicalTestValue;
use App\Response\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AddVitalSignController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate the request with custom messages
            $validated = $request->validate([
                'test_id' => 'required|exists:medical_test_values,id',
                'value' => 'required|numeric',
                'unit_id' => 'required|exists:units,id'
            ], [
                'test_id.required' => __('api.TestIdRequired'),
                'test_id.exists' => __('api.TestNotFound'),
                'value.required' => __('api.ValueRequired'),
                'value.numeric' => __('api.ValueMustBeNumeric'),
                'unit_id.required' => __('api.UnitIdRequired'),
                'unit_id.exists' => __('api.UnitNotFound')
            ]);

            // Check if the medical test value exists
            $medicalTestValue = MedicalTestValue::find($validated['test_id']);
            if (!$medicalTestValue) {
                return (new ApiResponse(404, __('api.TestNotFound'), []))->send();
            }

            // Validate value against min/max range if available
            if (isset($medicalTestValue->min) && isset($medicalTestValue->max)) {
                if ($validated['value'] < $medicalTestValue->min || $validated['value'] > $medicalTestValue->max) {
                    return (new ApiResponse(400, __('api.ValueOutOfRange'), [
                        'min' => $medicalTestValue->min,
                        'max' => $medicalTestValue->max
                    ]))->send();
                }
            }

            // Create the vital sign record
            $test = UserMedicalTest::create([
                'value' => $validated['value'],
                'unit_id' => $validated['unit_id'],
                'medical_test_value_id' => $validated['test_id'],
                'date' => Carbon::now()->toDateString(),
                'user_id' => auth()->id(),
            ]);

            if ($test) {
                return (new ApiResponse(201, __('api.VitalSignCreatedSuccessfully'), [
                    'id' => $test->id,
                    'value' => $test->value,
                    'date' => $test->date
                ]))->send();
            }

            return (new ApiResponse(400, __('api.FailedToCreateVitalSign'), []))->send();

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $firstError = collect($errors)->first()[0] ?? __('api.ValidationError');
            
            return (new ApiResponse(422, $firstError, [
                'errors' => $errors
            ]))->send();
            
        } catch (\Exception $e) {
            Log::error('Error in AddVitalSignController: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(500, __('api.ServerError'), []))->send();
        }
    }
}
