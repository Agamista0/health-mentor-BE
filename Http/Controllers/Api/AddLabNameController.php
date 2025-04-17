<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddLabNameController extends Controller
{
    public function index(Request $request){
        try {
            $request->validate([
                'lab_name' => 'required',
            ]);
    
            Lab::create([
                'name' => $request->lab_name,
            ]);
    
            return (new ApiResponse(200, __('LabCreatedSuccessfully'), []))->send();
        } catch (\Exception $e) {
            Log::error('Error creating Note: ' . $e->getMessage());
    
            return (new ApiResponse(500, __('api.ServerError'),[]))->send();
        }
    }
}
