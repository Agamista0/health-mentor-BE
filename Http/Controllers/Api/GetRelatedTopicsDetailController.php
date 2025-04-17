<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TopicResource;
use App\Models\Article;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetRelatedTopicsDetailController extends Controller
{
    public function index(Request $request){
        try {
            $id = $request->input('topic_id');
            $topic = Article::find($id);
    
            return (new ApiResponse(200, __('TopicRetrievedSuccessfully'), [
                'topic' => new TopicResource($topic)
            ]))->send();
        } catch (\Exception $e) {
            Log::error('Error retrieving examinations: ' . $e->getMessage());
    
            return (new ApiResponse(500, __('api.ServerError'),[]))->send();
        }
    }
}
