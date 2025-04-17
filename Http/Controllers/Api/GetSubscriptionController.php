<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Response\ApiResponse;
use Illuminate\Http\Request;

class GetSubscriptionController extends Controller
{
   public function index(){
      $subscriptions = Subscription::get();

      return(new ApiResponse(200,__('api.SubscriptionsRetrievedSuccessfully'),[
            'subscriptions'=>SubscriptionResource::collection($subscriptions)
         ]))->send();
   }
}
