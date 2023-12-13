<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\TrialTestController;
use App\Http\Controllers\ApiEnglishPlus\AdminApiController;
use App\Http\Controllers\ApiEnglishPlus\WebAppApiController;
use App\Http\Controllers\TrialTestController as studentTrialTestController;
use App\Http\Controllers\Jobs\JobsApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function () {
    Route::prefix('core')->group(function () {
        // non authentication
        Route::get('/health', [AuthController::class, 'health']);

        //admin-api-english-plus
        Route::post('/admin/trial-test/random-topic-by-level', [AdminApiController::class, 'randomTopicByLevel']);
        Route::get('/admin/trial-test/topic', [TrialTestController::class, 'dataTopic']);
        Route::get('/admin/trial-test/all-topic', [TrialTestController::class, 'dataTopics']);
        Route::get('/admin/trial-test/all-tags', [TrialTestController::class, 'dataTags']);
        Route::get('/get-session-test', [WebAppApiController::class, 'getSessionTest']);
        Route::post('/create-session-test', [WebAppApiController::class, 'createSessionTest']);
        Route::post('/update-session-test', [WebAppApiController::class, 'updateSessionTest']);
        Route::post('/get-information-of-topics-by-id', [AdminApiController::class, 'getInformationOfTopicsById']);
        Route::post('/admin/trial-test/recover-answer-for-student-test-question-result-table', [JobsApiController::class, 'recoverAnswerForStudentTestQuestionResultTable']);

        // have authentication
        Route::middleware(['auth-en-plus'])->group(function () {
            Route::get('/me', [AuthController::class, 'getMe']);

            // admin
            Route::get('/admin/me', [AuthController::class, 'getAdmin']);
            Route::middleware(['permission-en-plus:amltm_view', 'permission-en-plus:sz_lt_view'])->group(function () {
                Route::get('/admin/trial-test/data-topic', [TrialTestController::class, 'dataTopic']);
                Route::get('/admin/trial-test/data-topics', [TrialTestController::class, 'dataTopics']);
                Route::post('/admin/trial-test/data-questions', [TrialTestController::class, 'dataQuestions']);
                Route::post('/admin/trial-test/data-detail-question', [TrialTestController::class, 'dataDetailQuestion']);
                Route::middleware(['permission-en-plus:amltm_edit'])->group(function () {
                    Route::post('/admin/trial-test/data-questions-with-sort', [TrialTestController::class, 'dataQuestions']);
                    Route::post('/admin/trial-test/delete-questions', [TrialTestController::class, 'deleteQuestions']);
                    Route::post('/admin/trial-test/save-question', [TrialTestController::class, 'saveQuestion']);
                    Route::post('/admin/trial-test/delete-topics', [TrialTestController::class, 'deleteTopics']);
                    Route::post('/admin/trial-test/save-topic', [TrialTestController::class, 'saveTopic']);
                    Route::get('/admin/trial-test/data-section', [TrialTestController::class, 'dataSection']);
                    Route::post('/admin/trial-test/save-section', [TrialTestController::class, 'saveSection']);
                    Route::post('/admin/trial-test/delete-section', [TrialTestController::class, 'deleteSection']);
                });
            });
        });

        // Student
        Route::group(['prefix' => 'student'], function () {
            Route::get('/start-test', [studentTrialTestController::class, 'startTest']);
            Route::post('/save-test-results', [studentTrialTestController::class, 'saveTestResults']);

            // Route::middleware(['auth-en-plus', 'permission-en-plus:amltm_view'])->group(function () {
                Route::get('/start-pre-test', [studentTrialTestController::class, 'startTest']);
                // Route::middleware(['permission-en-plus:amltm_edit'])->group(function () {
                    Route::post('/save-results-pre-test', [studentTrialTestController::class, 'saveTestResults']);
                // });
            // });

            Route::get('/link-ielts-skills', [studentTrialTestController::class, 'getLinkIeltsSkills']);
        });
    });
});
