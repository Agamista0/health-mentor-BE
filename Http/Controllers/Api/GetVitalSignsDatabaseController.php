<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VitalSignDataBaseResource;
use App\Models\MedicalTest;
use App\Response\ApiResponse;
use Illuminate\Http\Request;

class GetVitalSignsDatabaseController extends Controller
{
    public function index(){
        // $database = MedicalTest::get();
        $database = MedicalTest::with('units.subUnits')->get();


        return (new ApiResponse(200, __('VitalSignDataBaseRetrievedSuccessfully'), [
            'database' => VitalSignDataBaseResource::collection($database)
        ]))->send();
    }
}
