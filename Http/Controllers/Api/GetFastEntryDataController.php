<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Response\ApiResponse;
use Illuminate\Http\Request;

class GetFastEntryDataController extends Controller
{
    public function index(){
        $types = ['weight', 'glucose', 'pressure', 'heartRate', 'relaxHeartRate', 'temp', 'height'];
        $user = auth()->user();
    
        $values = $user->UserMedicalTestValue->whereIn('type', $types);
        
        return (new ApiResponse(200, __('Reports Retrieved Successfully'), [
            'results' => $values->values()->toArray(), // Use the values() method to re-index the array
        ]))->send();  
    }
}
