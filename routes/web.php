<?php

use App\Http\Controllers\AjaxDatatableController;
use App\Http\Controllers\Dashboard\Age\AgeController;
use App\Http\Controllers\Dashboard\Coupons\CouponsController;
use App\Http\Controllers\Dashboard\Doctor\DoctorController;
use App\Http\Controllers\Dashboard\Notification\NotificationController;
use App\Http\Controllers\Dashboard\Plans\PlansController;
use App\Http\Controllers\Dashboard\Statistics\StatisticsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\Auth\AuthController;
use App\Http\Controllers\Dashboard\User\UserController;
use App\Http\Controllers\Dashboard\Admin\RoleController;
use App\Http\Controllers\Dashboard\Admin\AdminController;
use App\Http\Controllers\Dashboard\Answer\AnswerController;
use App\Http\Controllers\Dashboard\Article\ArticleController;
use App\Http\Controllers\Dashboard\ArticleDoctor\ArticleDoctorController;
use App\Http\Controllers\Dashboard\Examination\ExaminationController;
use App\Http\Controllers\Dashboard\ExaminationDetails\ExaminationDetailsController;
use App\Http\Controllers\Dashboard\MedicalTest\AdviceController;
use App\Http\Controllers\Dashboard\MedicalTest\MedicalTestController;
use App\Http\Controllers\Dashboard\MedicalTest\MedicalTestValueController;
use App\Http\Controllers\Dashboard\Result\RiskController;
use App\Http\Controllers\Dashboard\Question\QuestionController;
use App\Http\Controllers\Dashboard\Result\ResultController;
use App\Http\Controllers\Dashboard\Section\SectionController;
use App\Http\Controllers\Dashboard\Speciality\SpecialityController;
use App\Http\Controllers\Dashboard\SubUnit\SubUnitController;
use App\Http\Controllers\Dashboard\Unit\UnitController;
use App\Http\Controllers\Dashboard\ParentUnit\ParentUnitController;
use App\Http\Controllers\Dashboard\MeasurementUnit\MeasurementUnitController;
use App\Http\Controllers\Dashboard\LaboratoryTests\LaboratoryTestsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('clear_cache', function () {

    \Artisan::call('cache:clear');

    dd("Cache is cleared");

});
Route::get('/storage-link', function () {
    // Run the Artisan command to create the storage link
    Artisan::call('storage:link');

    // Check if the symbolic link was created successfully
    if (file_exists(public_path('storage'))) {
        return Response::json(['message' => 'Storage link created successfully'], 200);
    } else {
        return Response::json(['message' => 'Failed to create storage link'], 500);
    }
});

Route::get('/login', function () {
    return view('admin.Auth.login');
});

Route::group(['middleware' => 'guest:web'], function () {
    Route::get('/login', [AuthController::class, 'login_form'])->name('login_form');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('edit_profile', [AuthController::class, 'edit_profile'])->name('edit_profile');
});


Route::get('locale/{locale}', function ($locale) {
    session()->put('locale', $locale);
    return redirect()->back();
});

Route::group(['middleware' =>'auth:web'], function () {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [AuthController::class, 'dashboard'])->name('home');
    Route::get('profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('edit_profile', [AuthController::class, 'edit_profile'])->name('edit_profile');
    Route::get('sections/activation/{id}', [SectionController::class, 'activation'])->name('sections.activate');
    Route::get('/get_questions/{sectionId}', [ResultController::class, 'getQuestions']);
    Route::get('/dashboard/statistics', [StatisticsController::class, 'index']);
    Route::get('/notificationToUsers/{type}', [NotificationController::class, 'notificationToUsers']);
    Route::resource('plans', PlansController::class);
	Route::post('/change-plan-status', [PlansController::class, 'changeStatus'])->name('change-plan-status');
    Route::get('subscribedusers', [PlansController::class, 'subscribedusers'])->name('plans.subscribedusers');
    Route::resource('coupons', CouponsController::class);
    // ajax coupons
    Route::get('GetCouponsbyselect', [AjaxDatatableController::class, 'GetCouponsbyselect'])->name('GetCouponsbyselect');

    Route::get('GetUserExpiredPlans', [AjaxDatatableController::class, 'GetUserExpiredPlans'])->name('GetUserExpiredPlans');


    Route::resource('roles', RoleController::class);
    Route::resource('admin', AdminController::class);
    Route::resource('users', UserController::class);
    Route::resource('doctors', DoctorController::class);
    Route::resource('section', SectionController::class);
    Route::resource('question', QuestionController::class);
    Route::resource('answer', AnswerController::class);
    Route::resource('articles', ArticleController::class);
    Route::resource('medical_tests', MedicalTestController::class);
    Route::resource('medical_test_values', MedicalTestValueController::class);
    Route::resource('doctors-article', ArticleDoctorController::class);
    Route::resource('specialities', SpecialityController::class);
    Route::resource('advices', AdviceController::class);
    Route::resource('results', ResultController::class);
    Route::resource('risks', RiskController::class);
    Route::resource('examinations', ExaminationController::class);
    Route::resource('examination_details', ExaminationDetailsController::class);
    Route::resource('ages',AgeController::class);
    Route::resource('notification', NotificationController::class);
    Route::resource('units', UnitController::class);
    Route::resource('parents', ParentUnitController::class);
    Route::resource('sub', SubUnitController::class);
    Route::resource('measurement',MeasurementUnitController::class);
	
	Route::get('/laboratory_tests/{id}/show-pdf-modal', [LaboratoryTestsController::class, 'showPdfModal'])->name('laboratory_tests.showPdfModal');
    Route::post('/save-data', [LaboratoryTestsController::class, 'saveData'])->name('save-data');
    Route::get('getLaboratoryTestsData', [LaboratoryTestsController::class, 'getLaboratoryTestsData'])->name('getLaboratoryTestsData');  
    Route::get('/pdf-viewer/{filename}', [LaboratoryTestsController::class, 'showPdf'])->name('pdf-viewer');
    Route::post('/save-userMedicalTest', [LaboratoryTestsController::class, 'SaveUserMedicalTest'])->name('save-userMedicalTest');
    Route::get('/get-userMedicalTests', [LaboratoryTestsController::class, 'getUserMedicalTests'])->name('get-userMedicalTests');

    Route::resource('laboratory_tests', LaboratoryTestsController::class);
});
