<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BodyStatus;
use App\Models\BodyStatusDetail;
use App\Models\AnswerUser;
use App\Models\Avatar;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DeleteUserController extends Controller
{
    public function index($id)
    {
        try {
            // Validate the ID
            if (!is_numeric($id)) {
                return ApiResponse::error(
                    'Invalid user ID',
                    ['id' => ['The user ID must be a number']],
                    422
                );
            }

            // Check if trying to delete own account
            if ((int)$id === auth()->user()->id) {
                return ApiResponse::error(
                    'Operation not allowed',
                    ['user' => ['You are not allowed to delete your own account']],
                    403
                );
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                $user = User::where('id', $id)
                    ->where('user_id', auth()->user()->id)
                    ->first();

                if (!$user) {
                    return ApiResponse::error(
                        'User not found',
                        ['user' => ['Account not found or you are not authorized to delete it']],
                        404
                    );
                }

                // Delete related records first in correct order
                // 1. Delete body status details
                $bodyStatuses = BodyStatus::where('user_id', $user->id)->get();
                foreach ($bodyStatuses as $bodyStatus) {
                    BodyStatusDetail::where('body_status_id', $bodyStatus->id)->delete();
                }
                
                // 2. Delete body statuses
                BodyStatus::where('user_id', $user->id)->delete();
                
                // 3. Delete answer users
                AnswerUser::where('user_id', $user->id)->delete();
                
                // 4. Delete avatar
                Avatar::where('user_id', $user->id)->delete();

                // 5. Finally delete the user
                $user->delete();

                // Commit transaction
                DB::commit();

                return ApiResponse::success(
                    'Account has been deleted successfully',
                    null,
                    200
                );

            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                Log::error('User deletion failed: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('User deletion error: ' . $e->getMessage());
            return ApiResponse::error(
                'Failed to delete user',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
