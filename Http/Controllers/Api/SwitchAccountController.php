<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SwitchAccountController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:users,id'
            ]);

            // Start database transaction
            DB::beginTransaction();

            try {
                $user = User::findOrFail($validatedData['id']);
                $authUser = auth()->user();

                // Check if user has permission to switch to this account
                if (!$this->hasPermissionToSwitch($user, $authUser)) {
                    return ApiResponse::error(
                        'Permission denied',
                        ['account' => ["You don't have permission to switch to this account"]],
                        403
                    );
                }

                // Delete all tokens except the current one
                $authUser->tokens()->delete();
                
                // Generate a new token for the requested account
                $token = $user->createToken('API Token')->plainTextToken;

                // Commit transaction
                DB::commit();

                return ApiResponse::success(
                    'Account switched successfully',
                    [
                        'user' => new UserResource($user),
                        'token' => $token
                    ],
                    200
                );

            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                Log::error('Account switch failed: ' . $e->getMessage());
                throw $e;
            }

        } catch (ValidationException $e) {
            return ApiResponse::error(
                'Validation failed',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            Log::error('Account switch error: ' . $e->getMessage());
            return ApiResponse::error(
                'Failed to switch account',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Check if user has permission to switch to the target account
     *
     * @param User $targetUser
     * @param User $authUser
     * @return bool
     */
    private function hasPermissionToSwitch(User $targetUser, User $authUser)
    {
        return $targetUser->user_id === $authUser->id || 
               $authUser->user_id === $targetUser->id;
    }
}
