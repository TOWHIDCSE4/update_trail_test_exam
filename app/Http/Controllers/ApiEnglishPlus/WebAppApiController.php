<?php

namespace App\Http\Controllers\ApiEnglishPlus;

use App\Http\Controllers\Controller;
use App\Models\StudentTestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WebAppApiController extends Controller
{
    public function getSessionTest(Request $req)
    {
        Log::info('getSessionTest >>');
        Log::info($req->test_result_id);
        $testResultId = $req->test_result_id;
        $userOid = $req->user_Oid;
        $testType = $req->test_type;
        $studentTestResult = null;
        if (!$testResultId) {
            $whereColumn = 'user_oid';
            if ($testType == config('trial_test.test_type.staff_pre_test')) {
                $whereColumn = 'staff_oid';
            }

            $studentTestResult = StudentTestResult::where('id', $testResultId)->where($whereColumn, $userOid)->first();
        }
        Log::info('getSessionTest <<');
        return [
            'code' => '10000',
            'data' => $studentTestResult,
            'message' => 'success'
        ];
    }

    public function createSessionTest(Request $req)
    {
        Log::info('createSessionTest >>');
        Log::info($req->test_result_id);
        try {
            $idTopic = $req->test_topic_id;
            $userOid = $req->user_Oid;
            $testType = $req->test_type;
            $urlCallback = $req->url_callback;
            $resultType = $req->result_type ? $req->result_type : 0;
            $studentTestResult = null;
            $code = uniqid('', true);

            if ($testType == config('trial_test.test_type.staff_pre_test')) {
                $userOid = auth('api')->user()->_id;

                // $studentTestResult = StudentTestResult::where('staff_oid', $userOid)
                // ->where('library_test_id', $idTopic)
                // ->orderBy('created_at', 'DESC')->first();

                // if (!$studentTestResult) {
                $studentTestResult = new StudentTestResult();
                $studentTestResult->library_test_id = $idTopic;
                $studentTestResult->code = $code;
                $studentTestResult->staff_oid = $userOid;
                $studentTestResult->test_type = config('trial_test.test_type.staff_pre_test');
                // $studentTestResult->extra_data = 'api/v1/core/student/start-pre-test';
                // $studentTestResult->url_callback = '/api/v1/core/student/lessons/update-test-result';
                $studentTestResult->result_type = $resultType;
                $studentTestResult->save();
                // }
            } else {
                $studentTestResult = new StudentTestResult();
                $studentTestResult->library_test_id = $idTopic;
                $studentTestResult->code = $code;
                $studentTestResult->user_oid = $userOid;
                // $studentTestResult->extra_data = 'api/v1/core/student/start-test';
                $studentTestResult->url_callback = $urlCallback;
                $studentTestResult->result_type = $resultType;
                $studentTestResult->save();
            }
            
            $data = $studentTestResult;
            $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();
            $data->topic_name = $libraryTest->topic;
            Log::info('createSessionTest <<');
            return [
                'code' => '10000',
                'data' => $data,
                'message' => 'success'
            ];
        } catch (\Exception $e) {
            Log::error('createSessionTest error');
            Log::error($e);
            return [
                'code' => '10001',
                'data' => null,
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function updateSessionTest(Request $req)
    {
        Log::info('updateSessionTest >>');
        Log::info($req->test_result_id);
        try {
            $userOid = $req->user_Oid;
            $idStudentTestResult = $req->test_result_id;
            $updateData = $req->update_data;

            StudentTestResult::where('id', $idStudentTestResult)
                ->where('user_oid', $userOid)
                ->update($updateData);

            $studentTestResult = StudentTestResult::where('id', $idStudentTestResult)->where('user_oid', $userOid)->first();

            Log::info('updateSessionTest <<');
            return [
                'code' => '10000',
                'data' => $studentTestResult,
                'message' => 'success'
            ];
        } catch (\Exception $e) {
            Log::error('updateSessionTest error');
            Log::error($e);
            return [
                'code' => '10001',
                'data' => null,
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }
}
