<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VitalSignTestResource;
use App\Models\UserMedicalTest;
use App\Response\ApiResponse;
use Illuminate\Http\Request;

class GetVitalSignsTestsController extends Controller
{
    public function index(){
        $tests = UserMedicalTest::where('user_id', auth()->user()->id)->get();

        return (new ApiResponse(200, __('VitalSignTestsRetrievedSuccessfully'), [
            'database' => VitalSignTestResource::collection($tests)
        ]))->send();
    }
}
