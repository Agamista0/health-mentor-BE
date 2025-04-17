<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionsBenefitResource;
use App\Models\SubscriptionBenefit;
use App\Response\ApiResponse;
use Illuminate\Http\Request;

class GetSubscriptionBenefitsController extends Controller
{
    public function index(){
        $benefits = SubscriptionBenefit::all();

        return (new ApiResponse(200, __('SubscriptionBenefitsRetrievedSuccessfully'), [
            'benefits' => SubscriptionsBenefitResource::collection($benefits)
        ]))->send();
    }
}
