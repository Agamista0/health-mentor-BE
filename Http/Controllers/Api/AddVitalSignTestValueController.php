<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserMedicalTest;
use App\Response\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AddVitalSignTestValueController extends Controller
{
    public function index(Request $request){
        $request->validate([
            'test_id' => 'required',
            'value' => 'required',
            'date' => 'required'
        ]);

        $test = UserMedicalTest::create([
            'value' => $request->value,
            'medical_test_value_id' => $request->test_id,
            'date' => $request->date,
            'user_id' => auth()->user()->id,
        ]);

        if($test){
            return (new ApiResponse(200, __('VitalSignCreatedSuccessfully'), []))->send();
        }
        return (new ApiResponse(400, __('BadRequest'), []))->send();
    }
}
