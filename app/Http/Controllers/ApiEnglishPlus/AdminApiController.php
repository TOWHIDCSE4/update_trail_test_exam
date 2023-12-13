<?php

namespace App\Http\Controllers\ApiEnglishPlus;

use App\Http\Controllers\Controller;
use App\Models\LibraryTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminApiController extends Controller
{
    // api trial test call by I-speak

    public function randomTopicByLevel(Request $request){
        Log::info('Random topic by level E+ >>');
        Log::info($request->levelId);
        try {
            $level_id = $request->levelId;
            $data = LibraryTest::query()->select('id')->where('level', $level_id)->where('folder', 'trial')->where('publish_status', config('trial_test.library_test.publish_status.published'))->orderByRaw('RAND()')->first();
            Log::info('Random topic by level E+ <<');
            return [
                'code' => '10000',
                'data' => $data->id,
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('Random topic by level E+ error');
            Log::error($e);
            return [
                'code' => '10001',
                'data' => null,
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function getInformationOfTopicsById(Request $req)
    {
        Log::info('getInformationOfTopicsById >>');
        Log::info($req->test_topic_ids);
        try {
            $testTopicIds = $req->test_topic_ids;
            $libraryTest = DB::table('library_test')->whereIn('id', $testTopicIds)->get()->toArray();
            Log::info('getInformationOfTopicsById <<');
            return [
                'code' => '10000',
                'data' => $libraryTest,
                'message' => 'success'
            ];
        } catch (\Exception $e) {
            Log::error('getInformationOfTopicsById error');
            Log::error($e);
            return [
                'code' => '10001',
                'data' => null,
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }
}
