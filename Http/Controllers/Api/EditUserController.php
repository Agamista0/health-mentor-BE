<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EditUserController extends Controller
{
    public function index(Request $request){
        $validatedData = $this->valid($request->all());

        if($validatedData->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validatedData->errors()
            ], 401);
        }
        $user = User::find(auth()->user()->id);
        
        if($user){
           $userData = [
                'full_name' => $request->name,
                'gender' => $request->gender,
                'age' => $request->age,
            ];
    
            $avatarData = [
                'skin' => $request->skin,
                'eye_color' => $request->eye_color,
                'hair_style' => $request->hair_style,
                'hair_color' => $request->hair_color,
            ];
        
            $user->update($userData);
            $user->avatar()->update($avatarData);
        }
        return response()->json([
            'status' => true,
            'message' => 'your Account is Updated Successfully',
        ], 200);
    }
    
    
    
    public function valid($requestData)
    {
        $validator = Validator::make($requestData, [
            'gender' => 'required|integer|in:0,1',
            'name' => 'required|string|max:255',
            'skin' => 'required|integer|in:0,1,2',
            'eye_color' => 'required|integer|in:0,1',
            'hair_style' => 'required|integer|in:0,1,2,3',
            'hair_color' => 'required|integer|in:0,1,2,3',
            'age' => 'required',
        ]);

        return $validator;
    }
    
}