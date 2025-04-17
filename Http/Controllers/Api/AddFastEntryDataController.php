<?php

namespace App\Http\Controllers\Api;

use App\Enums\TypeEnum;
use App\Http\Controllers\Controller;
use App\Models\UserMedicalTest;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AddFastEntryDataController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
          'type' => 'required|in:weight,glucose,pressure,heartRate,relaxHeartRate,temp,height',

            'date' => 'required',
            'value' => 'required|numeric',
            'value2' => [
                'nullable',
                Rule::when($request->input('type') === 'pressure', 'required'),
                'numeric',
            ],
            //'unit_id' => 'required|exists:units,id',
        ]);

        UserMedicalTest::create($request->all()+ [
            'user_id' => auth()->user()->id    
        ]);

        return (new ApiResponse(200, __('EntryDataCreatedSuccessfully'), []))->send();
    }
}
