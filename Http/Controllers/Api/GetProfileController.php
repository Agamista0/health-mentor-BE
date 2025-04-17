<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetProfileController extends Controller
{
    /**
     * Get authenticated user's profile with related data
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Get authenticated user ID
            $userId = auth()->id();

            // Use cache to improve performance
            $cacheKey = "user_profile_{$userId}";
            
            return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($userId) {
                // Get user with necessary relationships
                $account = User::with(['avatar', 'bodyStatus', 'subscriptionUser'])
                    ->findOrFail($userId);

                // Set current flag
                $account->is_current = 1;

                // Return formatted response
                return (new ApiResponse(
                    200,
                    __('messages.account_retrieved_successfully'),
                    ['profile' => new AccountResource($account)]
                ))->send();
            });

        } catch (ModelNotFoundException $e) {
            return (new ApiResponse(
                404,
                __('messages.user_not_found'),
                ['error' => $e->getMessage()]
            ))->send();

        } catch (\Exception $e) {
            return (new ApiResponse(
                500,
                __('messages.server_error'),
                ['error' => $e->getMessage()]
            ))->send();
        }
    }

    /**
     * Clear user profile cache
     * 
     * @param int $userId
     * @return void
     */
    private function clearProfileCache(int $userId): void
    {
        Cache::forget("user_profile_{$userId}");
    }
}
