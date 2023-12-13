<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryQuestion;
use App\Models\LibraryQuestionMatchingDetail;
use App\Models\LibraryQuestionQnADetail;
use App\Models\LibraryTest;
use App\Models\MAdmin;
use App\Models\Section;
use App\Models\SectionQuestion;
use App\Models\StudentTestQuestionResult;
use App\Models\StudentTestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\CommonMark\Parser\Block\ParagraphParser;
use Symfony\Component\Console\Question\Question;

class TrialTestController extends Controller
{
    public function dataTopics(Request $req){
        Log::info('List topic library test >>');
        try {
            $creatorOid = null;
            $user_oid = null;
            $publish_status = $req->publish_status;

            if (!$publish_status || $publish_status != 'published') {
                Log::info('Admin action:' . auth('api')->user()->_id);
                $creatorOid = $req->creator_oid;
                $user_oid = auth('api')->user()->_id;
            }
            $data = LibraryTest::query();
            $arrayRolePermission = [];
            $ePAdmin = MAdmin::find($user_oid);
            if ($user_oid && $ePAdmin) {
                $dataDepartmentAdmin = MAdmin::query()->where('_id',$user_oid)->value('department');
                $roleAdmin = $dataDepartmentAdmin['isRole'];
                $distinctCreator = LibraryTest::query()->select('creator_id')->distinct('creator_id')->get();
                $listAdminViewAll = explode(',', env('TEST_TOPIC_LIST_USER_CAN_VIEW_ALL'));
                if(in_array($user_oid, $listAdminViewAll)){
                    $roleAdmin = 'manager';
                }
                switch ($roleAdmin){
                    case 'manager':
                        foreach ($distinctCreator as $value){
                            $arrayRolePermission[] = $value->creator_id;
                        }
                        break;
                    case 'leader':
                        foreach ($distinctCreator as $value){
                            $dataDepartmentSubAdmin = MAdmin::query()->where('_id',$value->creator_id)->value('department');
                            if($dataDepartmentSubAdmin) {
                                $roleSubAdmin = $dataDepartmentSubAdmin['isRole'];
                                if (($roleSubAdmin == 'staff' || ($roleSubAdmin == 'leader' && $value->creator_id == $user_oid))) {
                                    $arrayRolePermission[] = $value->creator_id;
                                }
                            }
                        }
                        break;
                    case 'staff':
                        foreach ($distinctCreator as $value){
                            if($value->creator_id == $user_oid) {
                                $arrayRolePermission[] = $value->creator_id;
                            }
                        }
                        break;
                }
                if($creatorOid && $arrayRolePermission && count($arrayRolePermission) > 0) {
                    if(in_array($creatorOid, $arrayRolePermission)){
                        $arrayRolePermission = [];
                        $arrayRolePermission[] = $creatorOid;
                    }else{
                        $arrayRolePermission = [];
                    }
                }
                Log::info('array permission'. json_encode($arrayRolePermission));
                $data = $data->whereIn('creator_id', $arrayRolePermission);
            }

            $folderFilter = $req->folder_filter;
            $testTypeFilter = $req->test_type_filter;
            $statusFilter = $req->status_filter;
            $nameFilter = $req->name_filter;
            $search = $req->search;
            $tagsFilter = $req->tags_filter;
            $data = $data->when(!empty($folderFilter), function ($query) use ($folderFilter) {
                    $query->where('folder', $folderFilter);
                })
                ->when(!empty($testTypeFilter), function ($query) use ($testTypeFilter) {
                    $query->where('test_type', $testTypeFilter);
                })
                ->when(!empty($statusFilter), function ($query) use ($statusFilter) {
                    $query->where('publish_status', $statusFilter);
                })
                ->when(!empty($nameFilter), function ($query) use ($nameFilter) {
                    $query->where('topic','LIKE', '%'.$nameFilter.'%');
                })
                ->when((!empty($publish_status) && $publish_status == 'published'), function ($query) {
                    $query->where('publish_status', config('trial_test.library_test.publish_status.published'));
                })
                ->when(!empty($search), function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('topic','LIKE', '%'.$search.'%')
                            ->orWhere('id','LIKE', '%'.$search.'%');
                    });
                })
                ->when(!empty($tagsFilter), function ($query) use ($tagsFilter) {
                    foreach ($tagsFilter as $tag){
                        $query->whereRaw('FIND_IN_SET(?, tags)', [$tag]);
                    }
                })
                ->paginate(10);

            $dataListCreator = [];
            if (count($arrayRolePermission)) {
                $dataListCreator = MAdmin::query()->select('_id','fullname')->whereIn('_id',$arrayRolePermission)->get()->toArray();
            }

            foreach ($data as $item){
                if (count($dataListCreator)) {
                    $keyName =  array_search($item->creator_id, array_column($dataListCreator,'_id'));
                    $item->name_creator = $dataListCreator[$keyName]['fullname'];
                    Log::info('name creator'. $item->name_creator);
                }

                $item->number_attended = StudentTestResult::query()->where('library_test_id', $item->id)
                    ->whereNotNull('end_at')->where('test_type', config('common.test_type_result_trial_test.normal'))
                    ->distinct('user_oid')->count();
                if($item->number_attended > 0) {
//                    $idTestResult = StudentTestResult::query()->where('library_test_id', $item->id)->whereNotNull('end_at')->where('test_type', config('common.test_type_result_trial_test.normal'))->value('id');
//                    $dataQuestion = LibraryQuestion::query()->where('library_test_id', $item->id)->get();
//                    $maxScore = 0;
//                    foreach ($dataQuestion as $question){
//                        $totalSubScore = StudentTestQuestionResult::query()->where('student_test_result_id', $idTestResult)->where('question_id', $question->id)->value('total_sub_question_count');
//                        if($totalSubScore){
//                            $maxScore = $maxScore + ($totalSubScore * $question->scores);
//                        }
//                    }
//                    $item->max_scores = $maxScore;
//                    Log::info('data max score'. $maxScore);
//                    $total_scores = 0;
//                    $numberMaxScore = 0;
//                    $dataDistinct = StudentTestResult::query()->select('user_oid')->where('library_test_id', $item->id)->whereNotNull('end_at')->where('test_type', config('common.test_type_result_trial_test.normal'))->distinct('user_oid')->get();
//                    Log::info('data total'. $dataDistinct);
//                    foreach ($dataDistinct as $value) {
//                        $subDataDistinct = StudentTestResult::query()->where('library_test_id', $item->id)->where('user_oid', $value->user_oid)->whereNotNull('end_at')->where('test_type', config('common.test_type_result_trial_test.normal'))->first();
//                        if($subDataDistinct) {
//                            $total_scores += $subDataDistinct->scores;
//
//                            if($subDataDistinct->scores == $maxScore){
//                                $numberMaxScore++;
//                            }
//                        }
//                    }
                    $total_scores = 0;
                    $numberMaxScore = 0;
                    $item->max_scores = 1;
                    //
                    $item->total_scores = $total_scores;
                    $item->number_max_score = $numberMaxScore;
                    $percent = $item->number_max_score / $item->number_attended;
                    $item->percent_max_score = round($percent, 1) * 100;
                    $avg = $item->total_scores / $item->number_attended;
                        $item->average = round($avg, 1) ;
                    Log::info('avg topic'. $avg);
                }else {
                    $item->percent_max_score = 0;
                    $item->average = 0;
                }
            }
            Log::info('List topic library test <<');
            return [
                'code' => '10000',
                'data' => $data,
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('List topic library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function dataTags(Request $req){
        Log::info('List tags topic >>');
        try {
            $dataTags = env('LIST_TAG_TOPIC', null);
            if($dataTags){
                $dataTags = explode(",", $dataTags);
            }
            Log::info('List tags topic <<');
            return [
                'code' => '10000',
                'data' => $dataTags,
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('List tags topic error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function dataTopic(Request $request){
        Log::info('Topic library test >>');
        // Log::info('Admin action:' . auth('api')->user()->_id);
        try {
            $data = LibraryTest::query()->where('id', $request->id_topic)->first();
            if(empty($data)){
                return [
                    'code' => '10001',
                    'message' => 'Topic does not exists'
                ];
            }
            if($data->tags){
                $data->tags = explode(',', $data->tags);
            }

            Log::info('Topic library test <<');
            return [
                'code' => '10000',
                'data' => $data,
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('Topic library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function deleteTopics(Request $request){
        Log::info('Delete topic library test >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        DB::beginTransaction();
        try {
            $listTopicDelete = $request->list_topic;
            // check xem có student result chua, co roi thi canh bao
            $dataResult = StudentTestResult::query()->whereIn('library_test_id', $listTopicDelete)->get();
            if(!empty($dataResult) && count($dataResult) > 0){
                Log::info('exits student test result !');
                return [
                    'code' => '10002',
                    'message' => 'Topic đã có HV làm bài! Chỉ có thể chuyển sang dạng Draft.'
                ];
            }
//            $dataTopic = LibraryTest::query()->whereIn('id', $listTopicDelete)->get();
//            foreach ($dataTopic as $topic){
//                if($topic->section_ids){
//                    $arraySectionIds = explode(',', $topic->section_ids);
//                    foreach ($arraySectionIds as $sectionId){
//                        SectionQuestion::query()->where('section_id', $sectionId)->delete();
//                        Section::query()->where('id', $sectionId)->delete();
//                    }
//                }
//            }
            LibraryTest::query()->whereIn('id', $listTopicDelete)->delete();
            DB::commit();
            Log::info('Delete topic library test <<');
            return [
                'code' => '10000',
                'message' => 'success'
            ];
        }catch(\Exception $e){
            DB::rollback();
            Log::error('Delete topic library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function saveTopic( Request $request){
        Log::info('Save topic library test >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        try {
            $idTopic = $request->id_topic;
            $dataTag = null;
            if($request->tags && count($request->tags) > 0){
                $dataTag = implode(',', $request->tags);
            }
            if ($idTopic) {
                Log::info('Update');
                LibraryTest::where('id', $idTopic)
                ->update([
                    'topic' => $request->name_topic,
                    'folder' => $request->folder,
                    'test_type' => $request->test_type,
                    'level' => $request->level,
                    'test_time' => $request->test_time,
                    'publish_status' => $request->publish_status,
                    'tags' => $dataTag
                ]);
            } else {
                Log::info('Create');
                $checkTopic = LibraryTest::query()->where('topic', $request->name_topic)->first();
                if($checkTopic){
                    return [
                        'code' => '10001',
                        'message' => 'topic already exists'
                    ];
                }
                LibraryTest::create([
                        'topic' => $request->name_topic,
                        'folder' => $request->folder,
                        'test_type' => $request->test_type,
                        'level' => $request->level,
                        'test_time' => $request->test_time,
                        'publish_status' => $request->publish_status,
                        'creator_id' => auth('api')->user()->_id,
                        'tags' => $dataTag
                    ]);
            }

            Log::info('Save topic library test <<');
            return [
                'code' => '10000',
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('Save topic library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function dataQuestions(Request $request){
        Log::info('Get data list question library test >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        try {
            $type = $request->type;
            $topicId = $request->topic_id;
            $user_oid = auth('api')->user()->_id;
            $dataDepartmentAdmin = MAdmin::query()->where('_id',$user_oid)->value('department');
            $roleAdmin = $dataDepartmentAdmin['isRole'];
            $creatorId = LibraryTest::query()->where('id', $topicId)->value('creator_id');
            $dataDepartmentCreator = MAdmin::query()->where('_id',$creatorId)->value('department');
            $flagPermissionError = 1;
            $listAdminViewAll = explode(',', env('TEST_TOPIC_LIST_USER_CAN_VIEW_ALL'));
            Log::info('list admin view all: '. json_encode($listAdminViewAll));
            if(in_array($user_oid, $listAdminViewAll)){
                $roleAdmin = 'manager';
            }
            switch ($roleAdmin){
                case 'manager':
                    $flagPermissionError = 0;
                    break;
                case 'leader':
                        if($dataDepartmentCreator) {
                            $roleSubAdmin = $dataDepartmentCreator['isRole'];
                            if (($roleSubAdmin == 'staff' || ($roleSubAdmin == 'leader' && $creatorId == $user_oid))) {
                                $flagPermissionError = 0;
                            }
                        }
                    break;
                case 'staff':
                        if($dataDepartmentCreator && $creatorId == $user_oid) {
                            $flagPermissionError = 0;
                        }
                    break;
                default:
                    break;
            }
            if($flagPermissionError == 1) {
                return [
                    'code' => '10002',
                    'message' => 'No Permission'
                ];
            }
            if($type == 'sort'){
                $arraySort = $request->array_sort;
                $idSort = $request->ids_sort;
                $sectionId = $request->section_id;
                $maxOrderSort = max($arraySort);
                $minOrderSort = min($arraySort);
                $arraySortReal = [];
                for ($i = (int)($minOrderSort); $i <= (int)($maxOrderSort); $i++){
                    $arraySortReal[] = $i;
                }
                if($arraySort && $sectionId && count($arraySort) > 0 && $idSort && count($idSort) > 0){
                    foreach ($idSort as $index => $value){
                        SectionQuestion::query()->where('section_id', $sectionId)->where('question_id', $value)->update([
                           'question_order' => $arraySortReal[$index],
                        ]);
                    }
                }

            }
            $arraySection = [];
            $sections = LibraryTest::query()->where('id', $topicId)->first();

            Log::info('section list:'. $sections);
            if($sections && $sections->section_ids) {
                $arraySection = $sections->section_ids;
                $arraySection = explode(",", $arraySection);
            }
            Log::info('array section:'. json_encode($arraySection));
            $dataSection = Section::query()->whereIn('id', $arraySection)->get();
            Log::info('data section:'. $dataSection);
            foreach ($dataSection as $section) {
                $section->questions = LibraryQuestion::query()->join('section_question', 'library_question.id', '=', 'section_question.question_id')
                    ->where('section_question.section_id', $section->id)->orderBy('section_question.question_order', 'ASC')->get();
                foreach ($section->questions as $item) {
                    $item->number_attended = StudentTestResult::query()->where('library_test_id', $topicId)->whereNotNull('end_at')
                        ->where('test_type', config('common.test_type_result_trial_test.normal'))->distinct('user_oid')->count();
//                    $dataAttended = StudentTestResult::query()->select('user_oid')->where('library_test_id', $topicId)->whereNotNull('end_at')->where('test_type', config('common.test_type_result_trial_test.normal'))->distinct('user_oid')->get();
                    Log::info('number attended: ' . $item->number_attended);
                    $numberSubQuestion = $numberCorrect = 0;
//                foreach ($dataAttended as $value){
//                    $idResultFirst = StudentTestResult::query()->where('library_test_id', $topicId)->where('user_oid', $value->user_oid)->whereNotNull('end_at')->where('test_type', config('common.test_type_result_trial_test.normal'))->value('id');
//                    if($idResultFirst) {
//                        $numberCorrect += StudentTestQuestionResult::query()->where('question_id', $item->id)->where('student_test_result_id', $idResultFirst)->value('correct_count');
//                        $numberSubQuestion += StudentTestQuestionResult::query()->where('question_id', $item->id)->where('student_test_result_id', $idResultFirst)->value('total_sub_question_count');
//                    }
//                }
//                Log::info('number correct: '. $numberCorrect);
                    $item->number_correct = $numberCorrect;
                    $item->number_incorrect = $numberSubQuestion - $numberCorrect;
                }
            }

            Log::info('Get data list question library test <<');
            return [
                'code' => '10000',
                'data' => $dataSection,
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('Get data list question library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function deleteQuestions(Request $request){
        Log::info('Start delete section questions library test >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        DB::beginTransaction();
        try {
            $topicId = $request->topic_id;
            $sectionId = $request->section_id;
            $listQuestionDelete = $request->list_question;
            $data = SectionQuestion::query()->where('section_id', $sectionId)->whereIn('question_id', $listQuestionDelete)->delete();
//            foreach ($data as $question){
//                LibraryQuestionQnADetail::query()->where('library_question_id', $question->id)->delete();
//                LibraryQuestionMatchingDetail::query()->where('library_question_id', $question->id)->delete();
//                $question->delete();
//            }
            $dataSectionQuestionNew = SectionQuestion::query()->where('section_id', $sectionId)->orderBy('question_order', 'asc')->get();
            $index = 1;
            foreach ($dataSectionQuestionNew as $value) {
                $value->update([
                   'question_order' => $index
                ]);
                $index++;
            }
            DB::commit();
            Log::info('End delete section questions library test <<');
            return [
                'code' => '10000',
                'message' => 'success'
            ];
        }catch(\Exception $e){
            DB::rollBack();
            Log::error('delete questions library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function listQuestions(Request $request){
        $topic_id = $request->id;
        $name_topic = LibraryTest::query()->where('id', $topic_id)->value('topic');
        return view('admin.trial_test.list_questions', compact('topic_id', 'name_topic'));
    }

    public function editQuestion(Request $request){
        $question_id = $request->id;
        $topic_id = $request->topic_id;
        $section_id = $request->section_id;
        return view('admin.trial_test.create_question', compact('question_id', 'topic_id', 'section_id'));
    }

    public function dataDetailQuestion(Request $request){
        Log::info('Get data detail question library test >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        try {
            $question_id = $request->question_id;
            $data = LibraryQuestion::query()->where('id', $question_id)->first();
            $dataMatch = LibraryQuestionMatchingDetail::query()->where('library_question_id', $data->id)->get();
            $dataQna = LibraryQuestionQnADetail::query()->where('library_question_id', $data->id)->get();
            if($data && $data->main_type){
                $dataTypingCorrect = json_decode($data->correct_answer, true);
            }
                Log::info('Get data detail question library test <<');
            return [
                'code' => '10000',
                'data' => $data,
                'dataMatch' => $dataMatch,
                'dataQna' => $dataQna,
                'dataTypingCorrect' => $dataTypingCorrect,
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('Get data detail question library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function saveQuestion(Request $request){
        Log::info('Save question library test >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        DB::beginTransaction();
        try {
            $question_id = $request->question_id;
            $correctAnswer = $incorrectAnswer = null;
            if ($request->type_question == config('common.type_question_trial_test.sort')) {
                $correctAnswer = $request->correct_answer;
                $incorrectAnswer = $request->incorrect_answer;
            } else if ($request->type_question == config('common.type_question_trial_test.typing')) {
                $arrTypingAnsCorrect = json_decode($request->array_typing_answer_correct, true);
                $arrTyping = [];
                foreach ($arrTypingAnsCorrect as $value) {
                    $arrTyping[] = $value['content'];
                }
                $correctAnswer = json_encode($arrTyping);
            }
            if($request->word_minimum && $request->word_minimum == 'null') {
                $request->word_minimum = null;
            }
            if ($request->type_question == config('common.type_question_trial_test.dropdown')) {
                $correctAnswer = $request->correct_answer;
            }
            if ($question_id) {
                $data = LibraryQuestion::query()->where('id', $question_id)->update([
                    'title' => $request->title,
                    'audio_title' => $request->url_audio_title,
                    'voice_for_title' => $request->voice_title ?? 0,
                    'main_type' => $request->type_question,
                    'category' => $request->category,
                    'library_test_id' => $request->topic_id,
                    'scores' => $request->scores,
                    'picture_bellow_text' => $request->picture_bellow_text == 'true' ? 1 : 0,
                    'content_main_text' => ($request->type_question != config('common.type_question_trial_test.matching')
                        || $request->category ===  config('common.type_category_question.IELTS_Writing')) ? $request->content_question_main : null,
                    'voice_for_content_main' => $request->voice_content_main ?? 0,
                    'content_main_picture' => $request->type_question != config('common.type_question_trial_test.matching') ? $request->url_main_image : null,
                    'content_main_audio' => $request->type_question != config('common.type_question_trial_test.matching') ? $request->url_main_audio : null,
                    'correct_answer' => $correctAnswer,
                    'incorrect_answer' => $incorrectAnswer,
                    'voice_for_answer' => $request->voice_answer ?? 0,
                    'word_minimum' => $request->category ==  config('common.type_category_question.IELTS_Writing') ? $request->word_minimum : null,
                    'dropdown_list' => $request->type_question == config('common.type_question_trial_test.dropdown') ? $request->dropdown : null,
                    'flag_show_dropdown' => $request->type_question == config('common.type_question_trial_test.dropdown') ? $request->flag_show_dropdown : 0,
                    'editor_id' => auth('api')->user()->_id
                ]);

                if ($request->type_question == config('common.type_question_trial_test.matching') && $request->array_match_question) {
                    $arrMatchQuestion = json_decode($request->array_match_question, true);
                    $arrayIdQuestionMatch = [];
                    foreach ($arrMatchQuestion as $index => $value) {
                        if ($value['id']) {
                            $dataMatch = LibraryQuestionMatchingDetail::query()->where('id', $value['id'])->update([
                                'order' => $index + 1,
                                'content_text_a' => $value['tab_a'] == 1 ? $value['text_content_a'] : null,
                                'voice_for_content_text_a' => ($value['tab_a'] == 1 && $value['text_content_a'] && $value['voice_text_content_a'] == true &&
                                    $value['voice_text_content_a'] !== 'false') ? 1 : 0,
                                'picture_url_a' => $value['tab_a'] == 2 ? $value['image_url_a'] : null,
                                'content_text_b' => $value['tab_b'] == 1 ? $value['text_content_b'] : null,
                                'picture_url_b' => $value['tab_b'] == 2 ? $value['image_url_b'] : null,
                                'audio_url' => $value['audio_url'],
                            ]);
                            $arrayIdQuestionMatch[] = $value['id'];
                        } else {
                            $dataMatch = LibraryQuestionMatchingDetail::query()->create([
                                'library_question_id' => $question_id,
                                'order' => $index + 1,
                                'content_text_a' => $value['tab_a'] == 1 ? $value['text_content_a'] : null,
                                'voice_for_content_text_a' => ($value['tab_a'] == 1 && $value['text_content_a'] && $value['voice_text_content_a'] == true &&
                                    $value['voice_text_content_a'] !== 'false') ? 1 : 0,
                                'picture_url_a' => $value['tab_a'] == 2 ? $value['image_url_a'] : null,
                                'content_text_b' => $value['tab_b'] == 1 ? $value['text_content_b'] : null,
                                'picture_url_b' => $value['tab_b'] == 2 ? $value['image_url_b'] : null,
                                'audio_url' => $value['audio_url'],
                            ]);
                            $arrayIdQuestionMatch[] = $dataMatch->id;
                        }
                    }
                    if ($arrayIdQuestionMatch > 0) {
                        LibraryQuestionMatchingDetail::query()->where('library_question_id', $question_id)->whereNotIn('id', $arrayIdQuestionMatch)->delete();
                    }
                }
                if ($request->type_question == config('common.type_question_trial_test.choose') && $request->array_qna_question) {
                    $arrQnaQuestion = json_decode($request->array_qna_question, true);
                    $arrayIdQuestionQna = [];
                    foreach ($arrQnaQuestion as $index => $value) {
                        $arrayCorrectQna = [];
                        foreach ($value['correct'] as $subValue) {
                            $arrayCorrectQna[] = trim($subValue['subCorrect']);
                        }
                        $arrayIncorrectQna = [];
                        foreach ($value['incorrect'] as $subValue) {
                            $arrayIncorrectQna[] = trim($subValue['ic']);
                        }
                        if ($value['id']) {
                            $dataQna = LibraryQuestionQnADetail::query()->where('id', $value['id'])->update([
                                'order' => $index + 1,
                                'sub_question' => $value['content'],
                                'voice_for_sub_question' => ($value['voice_content'] == true && $value['voice_content'] !== 'false') ? 1 : 0,
                                'correct_answer' => implode('|', $arrayCorrectQna),
                                'incorrect_answer' => implode('|', $arrayIncorrectQna),
                                'voice_for_answer' => ($value['voice_answer'] == true && $value['voice_answer'] !== 'false') ? 1 : 0,
                            ]);
                            $arrayIdQuestionQna[] = $value['id'];
                        } else {
                            $dataQna = LibraryQuestionQnADetail::query()->create([
                                'library_question_id' => $question_id,
                                'order' => $index + 1,
                                'sub_question' => $value['content'],
                                'voice_for_sub_question' => ($value['voice_content'] == true && $value['voice_content'] !== 'false') ? 1 : 0,
                                'correct_answer'   => implode('|', $arrayCorrectQna),
                                'incorrect_answer' => implode('|', $arrayIncorrectQna),
                                'voice_for_answer' => ($value['voice_answer'] == true && $value['voice_answer'] !== 'false') ? 1 : 0,
                            ]);
                            $arrayIdQuestionQna[] = $dataQna->id;
                        }
                    }
                    if ($arrayIdQuestionQna > 0) {
                        LibraryQuestionQnADetail::query()->where('library_question_id', $question_id)->whereNotIn('id', $arrayIdQuestionQna)->delete();
                    }
                }

            } else {
                $current_order = DB::table('library_question')->where('library_test_id', $request->topic_id)->max('order');
                $data = LibraryQuestion::query()->create([
                    'title' => $request->title,
                    'audio_title' => $request->url_audio_title,
                    'voice_for_title' => $request->voice_title ?? 0,
                    'main_type' => $request->type_question,
                    'category' => $request->category,
                    'library_test_id' => $request->topic_id,
                    'scores' => $request->scores,
                    'order' => $current_order + 1,
                    'picture_bellow_text' => $request->picture_bellow_text == 'true' ? 1 : 0,
                    'content_main_text' => ($request->type_question != config('common.type_question_trial_test.matching') ||
                        $request->category ===  config('common.type_category_question.IELTS_Writing')) ? $request->content_question_main : null,
                    'voice_for_content_main' => $request->voice_content_main ?? 0,
                    'content_main_picture' => $request->type_question != config('common.type_question_trial_test.matching') ? $request->url_main_image : null,
                    'content_main_audio' => $request->type_question != config('common.type_question_trial_test.matching') ? $request->url_main_audio : null,
                    'correct_answer' => $correctAnswer,
                    'incorrect_answer' => $incorrectAnswer,
                    'voice_for_answer' => $request->voice_answer ?? 0,
                    'word_minimum' => $request->category ==  config('common.type_category_question.IELTS_Writing') ? $request->word_minimum : null,
                    'dropdown_list' => $request->type_question == config('common.type_question_trial_test.dropdown') ? $request->dropdown : null,
                    'flag_show_dropdown' => $request->type_question == config('common.type_question_trial_test.dropdown') ? $request->flag_show_dropdown : 0,
                    'creator_id' => auth('api')->user()->_id
                ]);
                $current_question_order = SectionQuestion::query()->where('section_id', $request->section_id)->max('question_order');

                SectionQuestion::query()->create([
                    'section_id' => $request->section_id,
                    'question_id' => $data->id,
                    'question_order' => $current_question_order + 1,
                    'creator_id' => auth('api')->user()->_id
                ]);

                if ($request->type_question == config('common.type_question_trial_test.matching') && $request->array_match_question) {
                    $arrMatchQuestion = json_decode($request->array_match_question, true);
                    foreach ($arrMatchQuestion as $index => $value) {
                        LibraryQuestionMatchingDetail::query()->create([
                            'library_question_id' => $data->id,
                            'order' => $index + 1,
                            'content_text_a' => $value['tab_a'] == 1 ? $value['text_content_a'] : null,
                            'voice_for_content_text_a' => ($value['tab_a'] == 1 && $value['text_content_a'] && $value['voice_text_content_a'] == true &&
                                $value['voice_text_content_a'] !== 'false') ? 1 : 0,
                            'picture_url_a' => $value['tab_a'] == 2 ? $value['image_url_a'] : null,
                            'content_text_b' => $value['tab_b'] == 1 ? $value['text_content_b'] : null,
                            'picture_url_b' => $value['tab_b'] == 2 ? $value['image_url_b'] : null,
                            'audio_url' => $value['audio_url'],
                        ]);
                    }
                }

                if ($request->type_question == config('common.type_question_trial_test.choose') && $request->array_qna_question) {
                    $arrQnaQuestion = json_decode($request->array_qna_question, true);
                    foreach ($arrQnaQuestion as $index => $value) {
                        $arrayCorrectQna = [];
                        foreach ($value['correct'] as $subValue) {
                            $arrayCorrectQna[] = trim($subValue['subCorrect']);
                        }
                        $arrayIncorrectQna = [];
                        foreach ($value['incorrect'] as $subValue) {
                            $arrayIncorrectQna[] = trim($subValue['ic']);
                        }
                        LibraryQuestionQnADetail::query()->create([
                            'library_question_id' => $data->id,
                            'order' => $index + 1,
                            'sub_question' => $value['content'],
                            'voice_for_sub_question' => ($value['voice_content'] == true && $value['voice_content'] !== 'false') ? 1 : 0,
                            'correct_answer' => implode('|', $arrayCorrectQna),
                            'incorrect_answer' => implode('|', $arrayIncorrectQna),
                            'voice_for_answer' => ($value['voice_answer'] == true && $value['voice_answer'] !== 'false') ? 1 : 0,
                        ]);
                    }

                }
            }
            DB::commit();
            Log::info('save question library test <<');
            return [
                'code' => '10000',
                'message' => 'success'
            ];
        }catch(\Exception $e){
            DB::rollback();
            Log::error('save question library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function saveSection( Request $request){
        Log::info('Save section >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        DB::beginTransaction();
        try {
            $idTopic = $request->id_topic;
            $idSection = $request->id_section;
            if ($idSection) {
                Log::info('Update');
                Section::query()->where('id', $idSection)
                    ->update([
                        'section_name' => $request->section_name,
                        'passage' => $request->passage,
                        'audio' => $request->audio ?? null,
                        'editor_id' => auth('api')->user()->_id,
                    ]);
            } else {
                Log::info('Create');
                $checkSection = Section::query()->where('id', $idSection)->first();
                if($checkSection){
                    return [
                        'code' => '10002',
                        'message' => 'section already exists'
                    ];
                }
                $sectionId = Section::query()->insertGetId([
                    'section_name' => $request->section_name,
                    'passage' => $request->passage,
                    'audio' => $request->audio ?? null,
                    'creator_id' => auth('api')->user()->_id,
                ]);
                $topic = LibraryTest::query()->where('id', $idTopic)->first();
                if($topic  && $sectionId) {
                    if($topic->section_ids) {
                        $listSectionIds = $topic->section_ids . ',' . $sectionId;
                    }else{
                        $listSectionIds = $sectionId;
                    }
                    $topic->update([
                        'section_ids' => $listSectionIds
                    ]);
                }
            }
            DB::commit();
            Log::info('Save section library test <<');
            return [
                'code' => '10000',
                'message' => 'success'
            ];
        }catch(\Exception $e){
            DB::rollback();
            Log::error('Save section library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function dataSection(Request $request){
        Log::info('Section library test >>');
        try {
            $data = Section::query()->where('id', $request->id_section)->first();
            if(empty($data)){
                return [
                    'code' => '10001',
                    'message' => 'Section does not exists'
                ];
            }

            Log::info('Section library test <<');
            return [
                'code' => '10000',
                'data' => $data,
                'message' => 'success'
            ];
        }catch(\Exception $e){
            Log::error('Topic library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

    public function deleteSection(Request $request){
        Log::info('Delete section library test >>');
        Log::info('Admin action:' . auth('api')->user()->_id);
        DB::beginTransaction();
        try {
            $topicId = $request->topicId;
            $sectionId = $request->section_id;
            $dataTopic = LibraryTest::query()->where('id', $topicId)->first();
            if($dataTopic && $dataTopic->section_ids){
                $arraySections = explode(',', $dataTopic->section_ids);
                foreach ($arraySections as $key => $item){
                    if($item == $sectionId){
                        unset($arraySections[$key]);
                    }
                }
                if(count($arraySections) > 0) {
                    $newArraySections = implode(',', $arraySections);
                }else{
                    $newArraySections = null;
                }
                $dataTopic->update([
                    'section_ids' => $newArraySections
                ]);
            }

            SectionQuestion::query()->where('section_id', $sectionId)->delete();
            Section::query()->where('id', $sectionId)->delete();
            DB::commit();
            Log::info('Delete topic library test <<');
            return [
                'code' => '10000',
                'message' => 'success'
            ];
        }catch(\Exception $e){
            DB::rollback();
            Log::error('Delete topic library test error');
            Log::error($e);
            return [
                'code' => '10001',
                'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
            ];
        }
    }

}
