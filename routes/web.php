<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TrialTestController;
use App\Http\Controllers\TrialTestController as studentTrialTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// admin trial test
Route::get('/admin/trial-test', function () {
    return view('admin.trial_test.index');
});

Route::get('/admin/trial-test/list-questions/{id}', [TrialTestController::class, 'listQuestions']);
Route::get('/admin/trial-test/create-question/{topic_id}/{section_id}', [TrialTestController::class, 'editQuestion']);
Route::get('/admin/trial-test/edit-question/{topic_id}/{section_id}/{id}', [TrialTestController::class, 'editQuestion']);


/**Trial Test **/
Route::group(['prefix' => 'student'], function () {
    Route::get("/trial-test", [studentTrialTestController::class, 'index'])->name('startTest');
    Route::get('/get-test-results', [studentTrialTestController::class, 'getTestResults']);
    Route::get('/get-trial-test-preview', [studentTrialTestController::class, 'getTrialTestPreview']);

    Route::get("/ielts-skill-synthesis", [studentTrialTestController::class, 'indexIeltsSkillSynthesis'])->name('ieltsSkillSynthesis');
});
