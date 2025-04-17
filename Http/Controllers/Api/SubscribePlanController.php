<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupons;
use App\Models\Subscription;
use App\Models\SubscriptionUser;
use App\Models\User;
use App\Response\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubscribePlanController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'plan_id' => 'required|integer|exists:subscriptions,id',
                'user_id' => 'required|integer|exists:users,id',
                'coupon' => 'nullable|string'
            ]);

            // Start database transaction
            DB::beginTransaction();

            try {
                // Get plan details
                $planDetails = Subscription::findOrFail($validatedData['plan_id']);
                
                // Check if user already has this subscription
                $existingSubscription = SubscriptionUser::where('subscription_id', $planDetails->id)
                    ->where('user_id', $validatedData['user_id'])
                    ->first();

                if ($existingSubscription) {
                    return ApiResponse::error(
                        'Subscription already exists',
                        ['subscription' => ['You already have this subscription plan']],
                        400
                    );
                }

                // Get user details
                $user = User::findOrFail($validatedData['user_id']);
                $planPrice = $planDetails->price;

                // Handle coupon if provided
                if (!empty($validatedData['coupon'])) {
                    $couponDetails = $this->validateAndApplyCoupon(
                        $validatedData['coupon'],
                        $planPrice
                    );

                    if ($couponDetails) {
                        $planPrice = $couponDetails['final_price'];
                    }
                }

                // Create subscription with required fields from the schema
                $subscription = SubscriptionUser::create([
                    'user_id' => $validatedData['user_id'],
                    'subscription_id' => $planDetails->id,
                    'create_date' => now(),
                    'end_date' => now()->addDays($planDetails->validity),
                    'subscription_ended' => 0,
                    'free_trial' => 0
                ]);

                // Increment coupon usage if applicable
                if (!empty($validatedData['coupon']) && isset($couponDetails)) {
                    $couponDetails['coupon']->increment('used', 1);
                }

                // Commit transaction
                DB::commit();

                return ApiResponse::success(
                    'Subscription created successfully',
                    [
                        'subscription' => [
                            'id' => $subscription->id,
                            'plan_id' => $subscription->subscription_id,
                            'user_id' => $subscription->user_id,
                            'start_date' => $subscription->create_date,
                            'end_date' => $subscription->end_date,
                            'subscription_ended' => $subscription->subscription_ended,
                            'free_trial' => $subscription->free_trial
                        ]
                    ],
                    201
                );

            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                Log::error('Subscription creation failed: ' . $e->getMessage());
                throw $e;
            }

        } catch (ValidationException $e) {
            return ApiResponse::error(
                'Validation failed',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            Log::error('Subscription creation error: ' . $e->getMessage());
            return ApiResponse::error(
                'Failed to create subscription',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Validate and apply coupon discount
     *
     * @param string $couponCode
     * @param float $originalPrice
     * @return array|null
     */
    private function validateAndApplyCoupon(string $couponCode, float $originalPrice)
    {
        $currentDate = Carbon::now()->toDateString();
        
        $coupon = Coupons::where('code', $couponCode)
            ->where('expiry_date', '>=', $currentDate)
            ->whereRaw('CAST(limit_user AS UNSIGNED) > CAST(used AS UNSIGNED)')
            ->first();

        if (!$coupon) {
            Log::error('Invalid or expired coupon: ' . $couponCode);
            return null;
        }

        $finalPrice = $originalPrice;
        
        if ($coupon->type == 'fixed') {
            $finalPrice = $originalPrice - $coupon->discount_value;
        } else {
            $discountAmount = ($originalPrice * $coupon->percent_value) / 100;
            $finalPrice = $originalPrice - $discountAmount;
        }

        return [
            'coupon' => $coupon,
            'original_price' => $originalPrice,
            'discount_amount' => $originalPrice - $finalPrice,
            'final_price' => $finalPrice
        ];
    }
}
