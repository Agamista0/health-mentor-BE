<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GetAccountsController extends Controller
{
    public function index()
    {
        try {
            // Get authenticated user
            $authUser = auth()->user();
            
            // Get accounts based on user type
            $accounts = $this->getUserAccounts($authUser);

            // Prepare response data
            $responseData = [
                'current_user' => new AccountResource($authUser),
                'other_accounts' => AccountResource::collection($accounts),
                'total_accounts' => count($accounts) + 1,
            ];

            return (new ApiResponse(
                200,
                __('AccountsRetrievedSuccessfully'),
                $responseData
            ))->send();

        } catch (\Exception $e) {
            Log::error('Account retrieval error: ' . $e->getMessage());
            return (new ApiResponse(
                500,
                __('FailedToRetrieveAccounts'),
                ['error' => $e->getMessage()]
            ))->send();
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
        $query = User::where('active', 1);

        if (isset($authUser->phone)) {
            // For users with phone (main accounts)
            $query->where('user_id', $authUser->id);
        } else {
            // For sub-accounts
            $query->where(function($q) use ($authUser) {
                $q->where('id', $authUser->user_id)
                  ->orWhere('user_id', $authUser->user_id);
            });
        }

        // Get accounts excluding current user
        return $query->get()->reject(function ($user) use ($authUser) {
            return $user->id == $authUser->id;
        });
    }
}
