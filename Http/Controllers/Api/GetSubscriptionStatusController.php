<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\SubscriptionStatusResource;
use App\Models\SubscriptionUser;
use App\Response\ApiResponse;
use Illuminate\Http\Request;

class GetSubscriptionStatusController extends Controller
{
    public function index(){
        $subscription = SubscriptionUser::where('user_id', auth()->user()->id)->first();
  
        if(isset($subscription)){
            return(new ApiResponse(200,__('api.SubscriptionRetrievedSuccessfully'),[
                  'subscription'=>new SubscriptionStatusResource($subscription)
               ]))->send();
        }

        return(new ApiResponse(200,__('Subscription Not Found'),[]))->send();
     }

}
