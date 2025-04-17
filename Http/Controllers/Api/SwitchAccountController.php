<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SwitchAccountController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                    function ($attribute, $value, $fail) {
                        $user = User::find($value);
                        if ($user && $user->active != 1) {
                            $fail('The selected account is inactive.');
                        }
                    }
                ]
            ], [
                'id.required' => 'Account ID is required',
                'id.exists' => 'The selected account does not exist',
                'id.integer' => 'Account ID must be an integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                $user = User::findOrFail($request->id);
                $currentUser = auth()->user();

                // Check if user has permission to switch to this account
                if (!($user->user_id === $currentUser->id || $currentUser->user_id === $user->id)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You do not have permission to switch to this account'
                    ], 403);
                }

                // Delete all tokens for current user
                $currentUser->tokens()->delete();

                // Create new token for the requested account
                $token = $user->createToken('API Token')->plainTextToken;

                // Commit transaction
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Account switched successfully',
                    'data' => [
                        'user' => new UserResource($user),
                        'token' => $token,
                        'token_type' => 'Bearer',
                        'expires_at' => now()->addDays(30)->toDateTimeString()
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Account switch failed: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Account switch error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to switch account',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }
}
