<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExaminationResource;
use App\Models\Examination;
use App\Response\ApiResponse;
use Illuminate\Support\Facades\Log;

class GetExaminationController extends Controller
{   
    public function index()
    {
        try {
            $examinations = Examination::with(['details' => function($query) {
                $query->select('examination_id', 'title', 'about');
            }])
            ->select('id', 'title', 'description')
            ->get();

            if ($examinations->isEmpty()) {
                return (new ApiResponse(200, __('api.NoExaminationsFound'), [
                    'examinations' => []
                ]))->send();
            }

            return (new ApiResponse(200, __('api.ExaminationsRetrievedSuccessfully'), [
                'examinations' => $examinations->map(function($exam) {
                    return [
                        'id' => $exam->id,
                        'title' => $exam->title,
                        'description' => $exam->description,
                        'image' => $exam->getFirstMediaUrl('images'),
                        'details' => $exam->details->map(function($detail) {
                            return [
                                'title' => $detail->title,
                                'details' => $detail->about
                            ];
                        })
                    ];
                })
            ]))->send();

        } catch (\Exception $e) {
            Log::error('Error in GetExaminationController: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return (new ApiResponse(500, __('api.ServerError'), [
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ]))->send();
        }
    }
}
