<?php

use App\Http\Controllers\Api\AddFastEntryDataController;
use App\Http\Controllers\Api\AddLabNameController;
use App\Http\Controllers\Api\AddNoteController;
use App\Http\Controllers\Api\AddUserController;
use App\Http\Controllers\Api\AddVitalSignController;
use App\Http\Controllers\Api\AddVitalSignTestValueController;
use App\Http\Controllers\Api\CheckCouponController;
use App\Http\Controllers\Api\CheckPhoneController;
use App\Http\Controllers\Api\DeleteFileController;
use App\Http\Controllers\Api\GetAccountsController;
use App\Http\Controllers\Api\GetSubscriptionController;
use App\Http\Controllers\Api\SubscribeDetailsController;
use App\Http\Controllers\Api\SubscribePlanController;
use App\Http\Controllers\Api\VerifyOTPController;
use App\Http\Controllers\Api\VerifyPhoneController;
use App\Http\Controllers\Api\GetBodyStatusController;
use App\Http\Controllers\Api\GetBodyStatusDetailsController;
use App\Http\Controllers\Api\GetExaminationController;
use App\Http\Controllers\Api\GetExaminationDetailController;
use App\Http\Controllers\Api\GetFileDetailsController;
use App\Http\Controllers\Api\GetFilesController;
use App\Http\Controllers\Api\GetHealthQuestionController;
use App\Http\Controllers\Api\GetOnBoardingQuestionController;
use App\Http\Controllers\Api\GetRelatedTopicsDetailController;
use App\Http\Controllers\Api\GetSectionQuestionsController;
use App\Http\Controllers\Api\GetSubscriptionBenefitsController;
use App\Http\Controllers\Api\GetSubscriptionStatusController;
use App\Http\Controllers\Api\GetUnitController;
use App\Http\Controllers\Api\GetVitalSignDatabaseDetailsController;
use App\Http\Controllers\Api\GetVitalSignsDatabaseController;
use App\Http\Controllers\Api\GetVitalSignsTestsController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\SwitchAccountController;
use App\Http\Controllers\Api\GetProfileController;
use App\Http\Controllers\Api\EditUserController;
use App\Http\Controllers\Api\DeleteUserController;
use App\Http\Controllers\Api\UploadReportController;
use App\Http\Controllers\Api\GetReportsController;
use App\Http\Controllers\Api\DeleteReportController;
use App\Http\Controllers\Api\GetFastEntryDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LaboratoryTestsController;
use App\Http\Controllers\Api\GetLaboratoryTestsFilesController;
use App\Http\Controllers\Api\GetUserMedicalTestController;
use App\Http\Controllers\Api\storeUserMedicalTestController;
use App\Http\Controllers\Api\AleafiaAssessmentController;
  

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', action: [LoginController::class, 'index']);
Route::post('verifyPhone', [VerifyPhoneController::class, 'index']);
Route::post('verifyOTP', [VerifyOTPController::class, 'index']);
Route::get('getOnBoardingQuestion', [GetOnBoardingQuestionController::class, 'index']);
Route::post('check_phone', [CheckPhoneController::class, 'checkPhone']);
Route::get('getSubscriptions', [GetSubscriptionController::class, 'index']);

Route::middleware(['auth:api'])->group(function () {
    // Route::middleware(["Token"])->group(function () {
        Route::get('getBodyStatus', [GetBodyStatusController::class, 'index']);
        Route::get('getHealthQuestions', [GetHealthQuestionController::class, 'index']);
        Route::get('getExaminations', [GetExaminationController::class, 'index']);
        Route::get('getExaminationDetails', [GetExaminationDetailController::class, 'index']);
        Route::get('getRelatedTopicsDetails', [GetRelatedTopicsDetailController::class, 'index']);
        Route::get('getSubscriptionStatus', [GetSubscriptionStatusController::class, 'index']);
        Route::get('getUnits', [GetUnitController::class, 'index']);
        Route::get('getFiles', [GetFilesController::class, 'index']);
        Route::get('getFileDetails', [GetFileDetailsController::class, 'index']);
        Route::post('addNotes', [AddNoteController::class, 'index']);
        Route::post('addLabName', [AddLabNameController::class, 'index']);
        Route::delete('deleteFile/{id}', [DeleteFileController::class, 'index']);
        Route::post('addFastEntryData', [AddFastEntryDataController::class, 'index']);
        Route::post('addUser', action: [AddUserController::class, 'index']);
        Route::get('getAccounts', [GetAccountsController::class, 'index']);
        Route::get('getVitalSignsDatabase', [GetVitalSignsDatabaseController::class, 'index']);
        Route::get('getVitalSignDatabaseDetails', [GetVitalSignDatabaseDetailsController::class, 'index']);
        Route::get('getSubscriptionBenefits', [GetSubscriptionBenefitsController::class, 'index']);
        Route::post('addVitalSign', [AddVitalSignController::class, 'index']);
        Route::post('addVitalSignTestValue', [AddVitalSignTestValueController::class, 'index']);
        Route::post('switchAccount', [SwitchAccountController::class, 'index']);
        Route::get('getVitalSignsTests', [GetVitalSignsTestsController::class, 'index']);
        Route::get('getSectionQuestions', [GetSectionQuestionsController::class, 'index']);
        Route::get('getBodyStatusDetails', [GetBodyStatusDetailsController::class, 'index']);
        Route::post('subscribeplan', [SubscribePlanController::class, 'index']);
        Route::post('checkCoupon', [CheckCouponController::class, 'index']);
        Route::get('subscribeDetails', [SubscribeDetailsController::class, 'index']);
        Route::get('getProfile', [GetProfileController::class, 'index']);
        Route::post('editUser', [EditUserController::class, 'index']);
        Route::delete('deleteUser/{id}', [DeleteUserController::class, 'index']);
        Route::post('uploadReport', [UploadReportController::class, 'index']);
        Route::get('getReports', [GetReportsController::class, 'index']);
        Route::delete('deleteReport/{id}', [DeleteReportController::class, 'index']);
        Route::get('getFastEntryData', [GetFastEntryDataController::class, 'index']);
	
	     /**LaboratoryTest */
        Route::post('uploadLaboratoryTest', [LaboratoryTestsController::class, 'index']);
        Route::get('GetLaboratoryTests', [GetLaboratoryTestsFilesController::class, 'index']);
        Route::get('GetUserMedicalTest', [GetUserMedicalTestController::class, 'index']);
        Route::post('storeUserMedicalTest', [storeUserMedicalTestController::class, 'index']);
    // });

        Route::get('/getAleafiaQuestions', [AleafiaAssessmentController::class, 'getQuestions']);
        Route::post('/submitAleafiaAssessment', [AleafiaAssessmentController::class, 'submitAssessment']);
        Route::get('/getUserAleafiaHistory', [AleafiaAssessmentController::class, 'getUserHistory']);

});

Route::middleware('auth:sanctum')->get('/user', action: function (Request $request) {
    return $request->user();
});


   
