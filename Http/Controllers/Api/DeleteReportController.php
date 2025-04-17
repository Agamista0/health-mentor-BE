<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Response\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\LaboratoryTests;
use Illuminate\Support\Facades\Storage;

class DeleteReportController extends Controller
{
    public function index($id)
    {
        try {
            // Find the laboratory report by its ID
            $report = LaboratoryTests::find($id);
            if (!$report) {
                // Return a 404 response if the report is not found
                return (new ApiResponse(404, __('ReportNotFound'), []))->send();
            }
            // Find all laboratory files associated with the given report ID
            $files = $report->files;

            // Delete each file from storage and database
            foreach ($files as $file) {
                // Delete the file from storage
                Storage::delete("public/{$file->file_path}");

                // Delete the file record from the database
                $file->delete();
            }

            // Delete the laboratory report record
            $report->delete();

            return (new ApiResponse(200, __('FilesDeletedSuccessfully'), []))->send();
        } catch (\Exception $e) {
            return (new ApiResponse(500, __('api.ServerError'), []))->send();
        }
    }
}
