<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BodyStatusDetailResource;
use App\Models\Answer;
use App\Models\AnswerResult;
use App\Models\Result;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetBodyStatusDetailsController extends Controller
{
    public function index(Request $request){
        $user = Auth::user();
       
        $allAnswerIds = array_reduce($request->questions, function ($carry, $item) {
            return array_merge($carry, $item['answers']);
        }, []);

        $answerResults = AnswerResult::whereIn('answer_id', $allAnswerIds)->get();

        $resultIds = $answerResults->pluck('result_id')->unique();

        if ($resultIds->isEmpty()) {
            return (new ApiResponse(200, __('No Result Found'), []))->send();
        }

        $body_status_details = $user->bodyStatus->Details->where('section_id', $request->section_id)->first();

        $result = Result::find(intVal($resultIds[0]));

        $healthDynamics = isset($body_status_details->health_dynamics)
        ? $body_status_details->health_dynamics
        : [];
    
        $healthDynamics[] = floatval($body_status_details->result->value);
        $body_status_details->update([
            'result_id' => $result->id,
            'health_dynamics' => $healthDynamics
        ]);
        
        return (new ApiResponse(200, __('No Result Found'), [
            'status_details' => new BodyStatusDetailResource($body_status_details)
        ]))->send();
    }
}
