<?php

namespace App\Http\Controllers\Api;

use App\Models\Note;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AddNoteController extends Controller
{
    public function index(Request $request){
       // dd(auth()->guard('api')->user());
        try {
            $request->validate([
                'note' => 'required',
            ]);
    
            Note::create([
                'note' => $request->note,
                'user_id' => auth()->user()->id
            ]);
    
            return (new ApiResponse(200, __('NoteCreatedSuccessfully'), [
            ]))->send();
        } catch (\Exception $e) {
            Log::error('Error creating Note: ' . $e->getMessage());
    
            return (new ApiResponse(500, __('api.ServerError'),[]))->send();
        }


    }
}
