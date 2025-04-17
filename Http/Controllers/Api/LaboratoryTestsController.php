<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryFiles;
use App\Models\LaboratoryTests;
use Illuminate\Http\Request;
use App\Response\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class LaboratoryTestsController extends Controller
{
    /**
     * Upload laboratory test files (PDF or Images)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     * @throws QueryException
     * @throws \Exception
     */
    public function index(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:pdf,image',
                'files' => 'required|array|min:1',
                'files.*' => $request->type === 'pdf' 
                    ? 'file|mimes:pdf|max:5120' 
                    : 'file|mimes:jpeg,png,jpg|max:5120'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = auth()->user();
            $uploadedFiles = [];

            // Start database transaction
            DB::beginTransaction();

            try {
                // Create a new LaboratoryTest entry
                $laboratoryTest = LaboratoryTests::create([
                    'user_id' => $user->id,
                    'fileType' => $request->type
                ]);

                // Process each file
                foreach ($request->file('files') as $file) {
                    $fileInfo = $this->processFile($file, $request->type, $laboratoryTest->id);
                    $uploadedFiles[] = $fileInfo;
                }

                DB::commit();

                return (new ApiResponse(
                    200,
                    __('messages.files_uploaded_successfully'),
                    [
                        'laboratory_test_id' => $laboratoryTest->id,
                        'files_count' => count($uploadedFiles),
                        'file_type' => $request->type,
                        'files' => $uploadedFiles
                    ]
                ))->send();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return (new ApiResponse(
                422,
                __('messages.validation_error'),
                ['errors' => $e->errors()]
            ))->send();
        } catch (QueryException $e) {
            Log::error('Database error in LaboratoryTestsController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return (new ApiResponse(
                500,
                __('messages.database_error'),
                []
            ))->send();
        } catch (\Exception $e) {
            Log::error('Error in LaboratoryTestsController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return (new ApiResponse(
                500,
                __('messages.server_error'),
                []
            ))->send();
        }
    }

    /**
     * Process and store a single file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $type
     * @param int $laboratoryTestId
     * @return array
     */
    private function processFile($file, string $type, int $laboratoryTestId): array
    {
        $extension = $file->getClientOriginalExtension();
        $newFileName = 'file_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $type === 'pdf' ? 'LaboratoryTests/PDF' : 'LaboratoryTests/Images';

        // Save the file
        $file->storeAs('public/' . $filePath, $newFileName);

        // Create file record
        $laboratoryFile = LaboratoryFiles::create([
            'laboratory_test_id' => $laboratoryTestId,
            'file_path' => $filePath . '/' . $newFileName,
            'file_type' => $type,
            'pdf_data' => null
        ]);

        return [
            'id' => $laboratoryFile->id,
            'file_path' => $laboratoryFile->file_path,
            'file_type' => $laboratoryFile->file_type,
            'created_at' => $laboratoryFile->created_at
        ];
    }
}
