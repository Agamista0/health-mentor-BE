<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthQuestionResource;
use App\Models\Question;
use App\Response\ApiResponse;
use Illuminate\Http\Request;

class GetHealthQuestionController extends Controller
{
    public function index(Request $request){
        $sectionName = $request->input('category');
        $questions = Question::whereHas('sections', function ($query) use ($sectionName) {
            $query->where('name', $sectionName);
        })->get();

        return(new ApiResponse(200,__('api.QuestionsRetrievedSuccessfully'),[
            'data'=>HealthQuestionResource::collection($questions)
         ]))->send();
    }
}
