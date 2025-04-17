<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetAccountsController extends Controller
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

            // Get accounts based on user type
            $accounts = $this->getUserAccounts($authUser);

            // Add current user to accounts list
            $authUser->is_current = 1;
            $accounts->push($authUser);

            // Return success response
            return ApiResponse::success(
                'Accounts retrieved successfully',
                [
                    'accounts' => AccountResource::collection($accounts)
                ],
                200
            );

        } catch (\Exception $e) {
            Log::error('Get accounts error: ' . $e->getMessage());
            return ApiResponse::error(
                'Failed to retrieve accounts',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get user accounts based on user type
     *
     * @param User $authUser
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUserAccounts(User $authUser)
    {
        $query = User::query();

        if (isset($authUser->phone)) {
            // If user has phone, get accounts where user_id matches
            $query->where('user_id', $authUser->id);
        } else {
            // If user doesn't have phone, get accounts where id or user_id matches
            $query->where(function($q) use ($authUser) {
                $q->where('id', $authUser->user_id)
                  ->orWhere('user_id', $authUser->user_id);
            });
        }

        $accounts = $query->get();

        // Remove current user from the list (will be added back later)
        return $accounts->reject(function ($user) use ($authUser) {
            return $user->id == $authUser->id;
        });
    }
}
