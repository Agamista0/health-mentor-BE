<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\FileUrlTrait;

class UserManagementController extends Controller
{
    use FileUrlTrait;

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 15; // minutes

    /**
     * Add a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z\s]+$/'
                ],
                'email' => [
                    'required',
                    'email',
                    'unique:users',
                    'max:255',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                ],
                'phone' => [
                    'required',
                    'string',
                    'unique:users',
                    'regex:/^\+?[1-9]\d{1,14}$/'
                ],
                'country_code' => [
                    'required',
                    'string',
                    'regex:/^\+\d{1,4}$/'
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
                ],
                'gender' => [
                    'required',
                    'in:male,female,other'
                ],
                'age' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:120'
                ],
                'address' => [
                    'nullable',
                    'string',
                    'max:500'
                ],
                'health_status' => [
                    'nullable',
                    'string',
                    'in:excellent,good,fair,poor'
                ],
                'description_disease' => [
                    'nullable',
                    'string',
                    'max:1000'
                ],
                'avatar' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif',
                    'max:2048'
                ]
            ], [
                'full_name.regex' => 'Full name should contain only letters and spaces',
                'email.regex' => 'Please enter a valid email address',
                'phone.regex' => 'Please enter a valid phone number',
                'country_code.regex' => 'Please enter a valid country code',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character',
                'health_status.in' => 'Health status must be one of: excellent, good, fair, poor'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    __('messages.validation_error'),
                    $validator->errors(),
                    422
                );
            }

            DB::beginTransaction();

            try {
                $user = User::create([
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'country_code' => $request->country_code,
                    'password' => Hash::make($request->password),
                    'gender' => $request->gender,
                    'age' => $request->age,
                    'address' => $request->address,
                    'health_status' => $request->health_status,
                    'description_disease' => $request->description_disease,
                    'api_token' => Str::random(60),
                ]);

                if ($request->hasFile('avatar')) {
                    $avatar = $request->file('avatar');
                    $path = $avatar->store('avatars', 'public');
                    
                    $user->avatar()->create([
                        'url' => $this->getRelativeFileUrl($path),
                        'path' => $path
                    ]);
                }

                $user->bodyStatus()->create([
                    'height' => 0,
                    'weight' => 0,
                    'bmi' => 0,
                    'body_fat' => 0,
                    'muscle_mass' => 0
                ]);

                DB::commit();

                Cache::forget("user_profile_{$user->id}");

                return $this->successResponse(
                    __('messages.user_created_successfully'),
                    [
                        'user' => new AccountResource($user),
                        'token' => $user->createToken('auth_token')->plainTextToken
                    ],
                    201
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'request_data' => $request->except(['password']),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                __('messages.server_error'),
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Edit an existing user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function editUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'full_name' => 'sometimes|string|max:255|regex:/^[a-zA-Z\s]+$/',
                'email' => [
                    'sometimes',
                    'email',
                    Rule::unique('users')->ignore($request->user_id),
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                ],
                'phone' => [
                    'sometimes',
                    'string',
                    Rule::unique('users')->ignore($request->user_id),
                    'regex:/^\+?[1-9]\d{1,14}$/'
                ],
                'country_code' => 'sometimes|string|regex:/^\+\d{1,4}$/',
                'password' => 'sometimes|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                'gender' => 'sometimes|in:male,female,other',
                'age' => 'sometimes|integer|min:1|max:120',
                'address' => 'nullable|string|max:500',
                'health_status' => 'nullable|string|in:excellent,good,fair,poor',
                'description_disease' => 'nullable|string|max:1000',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    __('messages.validation_error'),
                    $validator->errors(),
                    422
                );
            }

            $user = User::findOrFail($request->user_id);
            
            $updateData = $request->only([
                'full_name', 'email', 'phone', 'country_code',
                'gender', 'age', 'address', 'health_status',
                'description_disease'
            ]);

            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar->path);
                    $user->avatar()->delete();
                }

                $avatar = $request->file('avatar');
                $path = $avatar->store('avatars', 'public');
                
                $user->avatar()->create([
                    'url' => $this->getRelativeFileUrl($path),
                    'path' => $path
                ]);
            }

            $user->update($updateData);

            Cache::forget("user_profile_{$user->id}");

            return $this->successResponse(
                __('messages.user_updated_successfully'),
                ['user' => new AccountResource($user)]
            );

        } catch (\Exception $e) {
            Log::error('Error updating user', [
                'user_id' => $request->user_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                __('messages.server_error'),
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Delete avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar->path);
                $user->avatar()->delete();
            }

            $user->delete();

            Cache::forget("user_profile_{$id}");

            return $this->successResponse(
                __('messages.user_deleted_successfully'),
                []
            );

        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                __('messages.server_error'),
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get all accounts
     *
     * @return JsonResponse
     */
    public function getAccounts(): JsonResponse
    {
        try {
            $users = Cache::remember('users_list', 60, function () {
                return User::with(['avatar', 'bodyStatus', 'subscriptionUser'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            });

            return $this->successResponse(
                __('messages.accounts_retrieved_successfully'),
                ['accounts' => AccountResource::collection($users)]
            );

        } catch (\Exception $e) {
            Log::error('Error retrieving accounts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                __('messages.server_error'),
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Switch between accounts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function switchAccount(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    __('messages.validation_error'),
                    $validator->errors(),
                    422
                );
            }

            $user = User::findOrFail($request->user_id);
            
            Auth::user()->currentAccessToken()->delete();
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse(
                __('messages.account_switched_successfully'),
                [
                    'user' => new AccountResource($user),
                    'token' => $token
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error switching account', [
                'user_id' => $request->user_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                __('messages.server_error'),
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Helper method for success responses
     *
     * @param string $message
     * @param array $data
     * @param int $status
     * @return JsonResponse
     */
    private function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return (new ApiResponse($status, $message, $data))->send();
    }

    /**
     * Helper method for error responses
     *
     * @param string $message
     * @param array $errors
     * @param int $status
     * @return JsonResponse
     */
    private function errorResponse(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        return (new ApiResponse($status, $message, ['errors' => $errors]))->send();
    }

    public function uploadFile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    __('messages.validation_error'),
                    $validator->errors(),
                    422
                );
            }

            $path = $request->file('file')->store('public/files');
            return (new ApiResponse(200, __('api.FileUploadedSuccessfully'), [
                'url' => $this->getRelativeFileUrl($path),
            ]))->send();

        } catch (\Exception $e) {
            Log::error('Error uploading file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                __('messages.server_error'),
                ['error' => $e->getMessage()],
                500
            );
        }
    }
} 