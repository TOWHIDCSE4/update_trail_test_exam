<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use App\Models\StudentTestQuestionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JobsApiController extends Controller
{
    public function recoverAnswerForStudentTestQuestionResultTable(Request $req)
    {
        Log::info('recoverAnswerForStudentTestQuestionResultTable >>');
        try {
            $idQuestions = DB::table('library_question')->where('main_type', config('trial_test.main_type.q_and_a'))->pluck('id')->toArray();
            Log::info('idQuestions: ');
            Log::info($idQuestions);
            $lstStudentTestQuestionResult = DB::table('student_test_question_result')->whereIn('question_id', $idQuestions)->get();
            foreach ($lstStudentTestQuestionResult as $studentTestQuestionResult) {
                $answer = json_decode($studentTestQuestionResult->answer);
                if ($answer && count($answer) > 0 && !is_array($answer[0]) ) {
                    $newAnswer = [];

                    foreach ($answer as $value) {
                        array_push($newAnswer, [$value]);
                    }

                    StudentTestQuestionResult::where('id', $studentTestQuestionResult->id)
                    ->update([
                      'answer' => json_encode($newAnswer)
                    ]);
                }
            }
            Log::info('recoverAnswerForStudentTestQuestionResultTable <<');
            return [
                'code' => '10000',
                'message' => 'success'
            ];
        } catch (\Exception $e) {
            Log::error('recoverAnswerForStudentTestQuestionResultTable error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => $e
            ];
        }
    }
}
