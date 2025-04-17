<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionUser;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscribeDetailsController extends Controller
{
    public function index()
    {
        try {
            // Get the authenticated user
            $authUser = auth()->user();
            
            if (!$authUser) {
                return ApiResponse::error(
                    'Authentication failed',
                    ['auth' => ['User not authenticated']],
                    401
                );
            }

            // Get subscription details
            $subscriptionUser = SubscriptionUser::where('user_id', $authUser->id)
                ->where('end_date', '>=', now()) // Check if subscription is not expired
                ->first();

            if (!$subscriptionUser) {
                return ApiResponse::error(
                    'No active subscription found',
                    ['subscription' => ['You do not have an active subscription']],
                    404
                );
            }

            // Get plan details
            $planDetails = Subscription::where('id', $subscriptionUser->subscription_id)
                ->first();

            if (!$planDetails) {
                return ApiResponse::error(
                    'Plan not found',
                    ['plan' => ['The subscription plan could not be found']],
                    404
                );
            }

            // Prepare response data
            $responseData = [
                'subscription' => [
                    'id' => $subscriptionUser->id,
                    'user_id' => $subscriptionUser->user_id,
                    'subscription_id' => $subscriptionUser->subscription_id,
                    'start_date' => $subscriptionUser->start_date,
                    'end_date' => $subscriptionUser->end_date,
                    'is_active' => $subscriptionUser->end_date >= now(),
                ],
                'plan' => [
                    'id' => $planDetails->id,
                    'name' => $planDetails->name,
                    'description' => $planDetails->description,
                    'price' => $planDetails->price,
                    'duration' => $planDetails->duration,
                    'features' => $planDetails->features,
                ]
            ];

            return ApiResponse::success(
                'Subscription details retrieved successfully',
                $responseData,
                200
            );

        } catch (\Exception $e) {
            Log::error('Get subscription details error: ' . $e->getMessage());
            return ApiResponse::error(
                'Failed to retrieve subscription details',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
