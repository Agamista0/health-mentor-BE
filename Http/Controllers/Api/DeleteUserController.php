<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Avatar;
use App\Models\AnswerUser;
use App\Models\BodyStatus;
use App\Models\BodyStatusDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeleteUserController extends Controller
{
    public function index($id)
    {
        try {
            // Validate the ID
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer|exists:users,id'
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
                // Check if trying to delete own account
                if ($id == auth()->user()->id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You cannot delete your own account'
                    ], 403);
                }

                // Find the user with proper conditions
                $user = User::where('id', $id)
                    ->where('user_id', auth()->user()->id)
                    ->where('active', 1)
                    ->first();

                if (!$user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Account not found or you are not authorized to delete it'
                    ], 404);
                }

                // Soft delete the user
                $user->active = 0;
                $user->save();

                // Get related data before deletion for response
                $deletedUserData = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->full_name,
                    'deleted_at' => now()->toDateTimeString()
                ];

                // Commit transaction
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Account deleted successfully',
                    'data' => [
                        'deleted_user' => $deletedUserData,
                        'deleted_by' => [
                            'id' => auth()->user()->id,
                            'username' => auth()->user()->username
                        ],
                        'timestamp' => now()->toDateTimeString()
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('User deletion failed: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('User deletion error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete user',
                'errors' => ['error' => $e->getMessage()]
            ], 500);
        }
    }
}
