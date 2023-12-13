<?php

namespace App\Http\Controllers;

use App\Models\MAdmin;
use App\Models\MBooking;
use App\Models\MUser;
use Carbon\Carbon;
use App\Models\StudentTestResult;
use App\Models\StudentTestQuestionResult;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class TrialTestController extends Controller
{
  public function index(Request $req)
  {
    $trialTestToken = $req->trial_test_token ?? null;
    return view("user.trial_test.trial_test", ['trial_test_token' => $trialTestToken]);
  }

  public function indexIeltsSkillSynthesis(Request $req)
  {
    return view("user.trial_test.ielts_skill_synthesis", []);
  }

  public function getLinkIeltsSkills(Request $req)
  {
    Log::debug('getLinkIeltsSkills >>>');
    $ieltsSkillSynthesisCode = $req->ielts_skill_synthesis_code ?? null;
    $client = new Client();
    try {
      $url = env('BACKEND_API_URL') . '/api/v1/core/crm/student/trial-test-ielts-result/link-ielts-skills';
      Log::debug($url);
      $responseApi = $client->get($url, [
        'headers' => [
          'api-key' => env('BACKEND_API_APP_KEY')
        ],
        'query' => [
          'ielts_skill_synthesis_code' => $ieltsSkillSynthesisCode
        ]
      ]);

      $responseApiArray = json_decode($responseApi->getBody()->getContents(), true);
      Log::debug($responseApiArray);
      Log::debug('getLinkIeltsSkills <<<');
      return [
        'code' => '10000',
        'data' => $responseApiArray['data']
      ];
    } catch (\GuzzleHttp\Exception\ClientException $e) {
      Log::error('getLinkIeltsSkills - ERROR');
      Log::error($e);
      $msg = 'Lỗi hệ thống, hãy liên hệ với admin';

      $responseApiArray = json_decode($e->getResponse()->getBody()->getContents(), true);
      if ($responseApiArray['message']) {
        $msg = $responseApiArray['message'];
      }

      return [
        'code' => '10001',
        'message' => $msg
      ];
    }
  }

  private function str_replace_first($search, $replace, $subject)
  {
    $search = '/' . preg_quote($search, '/') . '/';
    $newStr = preg_replace($search, $replace, $subject, 1, $count);
    if (!$count) {
      return false;
    }

    return $newStr;
  }

  public function startTest(Request $req)
  {
    $data = null;
    $remainTime = null;
    $testCode = $req->test_code;
    $testType = $req->test_type;

    $remainTime = 0;
    $dateTimeNow = Carbon::now();

    StudentTestResult::where('code', $testCode)
      ->update([
        'test_start_time' => $dateTimeNow->format('Y-m-d H:i:s')
      ]);

    $studentTestResult = StudentTestResult::where('code', $testCode)->first();

    if (!$studentTestResult) {
      return [
        'code' => '10001',
        'message' => 'Can not take the test'
      ];
    }

    $idTopic = $studentTestResult->library_test_id;
    $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();

    if ($testType != config('trial_test.test_type.staff_pre_test')) {
      if ($libraryTest->publish_status != config('trial_test.library_test.publish_status.published')) {
        return [
          'code' => '10001',
          'message' => 'Can not take the test'
        ];
      }
    }

    $testStartTime = Carbon::parse($studentTestResult->test_start_time);
    $testedTime = $dateTimeNow->diffInSeconds($testStartTime);
    $idStudentTestResult = $studentTestResult->id;
    $testTimeSeconds = $libraryTest->test_time * 60;

    $remainTime = $testTimeSeconds - $testedTime;

    $topicTestType = $libraryTest->test_type;
    $data = [
      'id_student_test_result' => $idStudentTestResult,
      'isResult' => false,
      'topic_test_type' => $topicTestType,
      'sections' => []
    ];
    switch ($topicTestType) {
      case config('trial_test.topic.test_type.ielts_listening'):
      case config('trial_test.topic.test_type.ielts_reading'):
        $strSectionIds = $libraryTest->section_ids;
        $arrSectionIds = explode(",", $strSectionIds);
        $lstSection = DB::table('section')->whereIn('id', $arrSectionIds)->orderBy('id', 'ASC')->get();

        foreach ($lstSection as $section) {
          $sectionId = $section->id;
          $itemS = [
            'section_id' => $section->id,
            'section_name' => $section->section_name,
            'passage' => $section->passage,
            'audio' => $section->audio,
            'questions' => []
          ];

          if ($section->audio) {
            $itemS['play_audio'] = 0;
          }

          $lstSectionQuestion = DB::table('section_question')->join('library_question', 'library_question.id', '=', 'section_question.question_id')->where('section_id', $sectionId)->orderBy('question_order', 'ASC')->get();

          $keyQuestion = 0;
          foreach ($lstSectionQuestion as $questionVal) {
            switch ($questionVal->main_type) {
              case config('trial_test.main_type.sort'):
                $arrMerged = array_merge(explode("|", $questionVal->correct_answer), explode("|", $questionVal->incorrect_answer));
                shuffle($arrMerged);
                $arrShuffleAnswer = [];
                foreach ($arrMerged as $key => $value) {
                  array_push($arrShuffleAnswer, [
                    'answer' => $value,
                    'answer_play_audio' => 0
                  ]);
                }

                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'content_main_audio' => $questionVal->content_main_audio,
                  'content_main_picture' => $questionVal->content_main_picture,
                  'picture_bellow_text' => $questionVal->picture_bellow_text,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'voice_for_content_main' => $questionVal->voice_for_content_main,
                  'voice_for_answer' => $questionVal->voice_for_answer,
                  'correct_answer' => explode("|", $questionVal->correct_answer),
                  'incorrect_answer' => explode("|", $questionVal->incorrect_answer),
                  'shuffle_answer' => $arrShuffleAnswer,
                  'category' => $questionVal->category,
                  'title_play_audio' => 0,
                  'content_play_audio' => 0,
                  'contents' => []
                ];
                if ($questionVal->content_main_audio) {
                  $itemQ['play_audio'] = 0;
                }

                $contentMainText = $questionVal->content_main_text;
                if ($questionVal->category == config('trial_test.category.writing')) {
                  $countChartInContentMain = substr_count($contentMainText, "??");
                  $template = $contentMainText;

                  for ($i = 0; $i < $countChartInContentMain; $i++) {
                    $replaceTemplate = $this->str_replace_first("??", ' <div id="question-' . ($questionVal->id) . '-drag' . ($i + 1) . '_answer" class="container_drag mt-1 style_answer" draggable="true"> </div> ', $template);
                    $template = $replaceTemplate;
                  }

                  $template = str_replace("|", " ", $template);

                  $itemC = [
                    'template' => $template,
                    'complete_answer' => null,
                    'is_correct_answer' => null
                  ];
                  array_push($itemQ['contents'], $itemC);
                } else {
                  $countChartInContentMain = substr_count($contentMainText, "??");
                  $template = $contentMainText;

                  for ($i = 0; $i < $countChartInContentMain; $i++) {
                    $template = $this->str_replace_first("??", ' <div id="question-' . ($questionVal->id) . '-drag' . ($i + 1) . '_answer" class="container_drag mt-1 style_answer" draggable="true"> </div> ', $template);
                  }

                  $template = str_replace("|", " ", $template);
                  $itemC = [
                    'template' => $template
                  ];
                  array_push($itemQ['contents'], $itemC);
                }

                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
              case config('trial_test.main_type.q_and_a'):
                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'voice_for_content_main' => $questionVal->voice_for_content_main,
                  'content_main_text' => $questionVal->content_main_text,
                  'content_main_picture' => $questionVal->content_main_picture,
                  'content_main_audio' => $questionVal->content_main_audio,
                  'picture_bellow_text' => $questionVal->picture_bellow_text,
                  'title_play_audio' => 0,
                  'content_play_audio' => 0,
                  'sub_questions' => []
                ];
                if ($questionVal->content_main_audio) {
                  $itemQ['play_audio'] = 0;
                }

                $libraryQuestionQnaDetail = DB::table('library_question_qna_detail')->where('library_question_id', $questionVal->id)->orderBy('order', 'ASC')->get();
                foreach ($libraryQuestionQnaDetail as $qnaDetailKey => $qnaDetailVal) {
                  $qnaCorrectAnswer = explode("|", $qnaDetailVal->correct_answer);
                  $qnaInCorrectAnswer = explode("|", $qnaDetailVal->incorrect_answer);
                  $arrShuffle = [];
                  $mergeAnswer = array_merge($qnaCorrectAnswer, $qnaInCorrectAnswer);
                  shuffle($mergeAnswer);

                  foreach ($mergeAnswer as $keyAnswer => $answer) {
                    if ($answer) {
                      $itemShuffle = [
                        'answer' => $answer,
                        'answer_play_audio' => 0,
                      ];

                      array_push($arrShuffle, $itemShuffle);
                    }
                  }

                  $itemSubQ = [
                    'sub_question' => $qnaDetailVal->sub_question,
                    'voice_for_sub_question' => $qnaDetailVal->voice_for_sub_question,
                    'voice_for_answer' => $qnaDetailVal->voice_for_answer,
                    'subQ_play_audio' => 0,
                    'correct_answer' => $qnaCorrectAnswer,
                    'incorrect_answer' => $qnaInCorrectAnswer,
                    'shuffle_answer' => $arrShuffle,
                    'countChoose' => 0
                  ];
                  array_push($itemQ['sub_questions'], $itemSubQ);
                };

                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
              case config('trial_test.main_type.fill_input'):
                $arrContentMain = explode("\n", $questionVal->content_main_text);
                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'content_main_picture' => $questionVal->content_main_picture,
                  'content_main_text' => $questionVal->content_main_text,
                  'content_main_audio' => $questionVal->content_main_audio,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'title_play_audio' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'arr_content_main' => $arrContentMain,
                  'picture_bellow_text' => $questionVal->picture_bellow_text,
                  'contents' => []
                ];
                if ($questionVal->content_main_audio) {
                  $itemQ['play_audio'] = 0;
                }
                $idxTemplate = 0;
                foreach ($arrContentMain as $key => $value) {
                  $runWhile = true;
                  $contentMain = $arrContentMain[$key];
                  $template = $contentMain;
                  $idxCharInContent = 0;
                  while ($runWhile) {
                    if ($idxCharInContent >= substr_count($contentMain, "??")) {
                      $runWhile = false;
                      break;
                    }

                    $replaceTemplate = $this->str_replace_first("??", ' <span id="question-' . ($questionVal->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class="custom_fill_input" contenteditable></span> ', $template);
                    if ($replaceTemplate) {
                      $template = $replaceTemplate;
                      $idxTemplate += 1;
                    } else {
                      $runWhile = false;
                    }

                    $idxCharInContent += 1;
                  }

                  $template = str_replace("|", " ", $template);

                  $itemC = [
                    'template' => $template
                  ];
                  array_push($itemQ['contents'], $itemC);
                }

                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
              case config('trial_test.main_type.drop_down'):
                $arrContentMain = explode("\n", $questionVal->content_main_text);
                $lstOption = json_decode($questionVal->dropdown_list);

                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'content_main_picture' => $questionVal->content_main_picture,
                  'content_main_text' => $questionVal->content_main_text,
                  'content_main_audio' => $questionVal->content_main_audio,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'title_play_audio' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'arr_content_main' => $arrContentMain,
                  'picture_bellow_text' => $questionVal->picture_bellow_text,
                  'options' => $lstOption,
                  'flag_show_dropdown' => $questionVal->flag_show_dropdown,
                  'contents' => []
                ];
                if ($questionVal->content_main_audio) {
                  $itemQ['play_audio'] = 0;
                }
                $idxTemplate = 0;

                foreach ($arrContentMain as $key => $value) {
                  $runWhile = true;
                  $contentMain = $arrContentMain[$key];
                  $template = $contentMain;
                  $idxCharInContent = 0;
                  while ($runWhile) {
                    if ($idxCharInContent >= substr_count($contentMain, "??")) {
                      $runWhile = false;
                      break;
                    }

                    $dropdown = '<select class="form-control d-inline-block" id="question-' . ($questionVal->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" style="width: 100px !important">';
                    $dropdown .= '<option value=""></option>';
                    foreach ($lstOption as $key => $val) {
                      $dropdown .= '<option value=' . $val->value . '>' . $val->content . '</option>';
                    }
                    $dropdown .= '</select>';

                    $replaceTemplate = $this->str_replace_first("??", ' ' . $dropdown . ' ', $template);
                    if ($replaceTemplate) {
                      $template = $replaceTemplate;
                      $idxTemplate += 1;
                    } else {
                      $runWhile = false;
                    }

                    $idxCharInContent += 1;
                  }

                  $template = str_replace("|", " ", $template);

                  $itemC = [
                    'template' => $template
                  ];
                  array_push($itemQ['contents'], $itemC);
                }

                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
            }
          }

          array_push($data['sections'], $itemS);
        }

        break;

      case config('trial_test.topic.test_type.ielts_writing'):
        $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();
        $strSectionIds = $libraryTest->section_ids;
        $arrSectionIds = explode(",", $strSectionIds);
        $lstSection = DB::table('section')->whereIn('id', $arrSectionIds)->orderBy('id', 'ASC')->get();

        foreach ($lstSection as $section) {
          $sectionId = $section->id;
          $itemS = [
            'section_id' => $section->id,
            'section_name' => $section->section_name,
            'passage' => $section->passage,
            'questions' => []
          ];

          $lstSectionQuestion = DB::table('section_question')->join('library_question', 'library_question.id', '=', 'section_question.question_id')->where('section_id', $sectionId)->orderBy('question_order', 'ASC')->get();

          $keyQuestion = 0;
          foreach ($lstSectionQuestion as $questionVal) {
            $itemQ = [
              'main_type' => $questionVal->main_type,
              'content_main_picture' => $questionVal->content_main_picture,
              'content_main_text' => $questionVal->content_main_text,
              'content_main_audio' => $questionVal->content_main_audio,
              'question_id' => $questionVal->id,
              'title' => $questionVal->title,
              'audio_title' => $questionVal->audio_title,
              'title_play_audio_with_url' => 0,
              'title_play_audio' => 0,
              'voice_for_title' => $questionVal->voice_for_title,
              'picture_bellow_text' => $questionVal->picture_bellow_text,
              'word_minimum' => $questionVal->word_minimum
            ];
            if ($questionVal->content_main_audio) {
              $itemQ['play_audio'] = 0;
            }

            array_push($itemS['questions'], $itemQ);
            $keyQuestion += 1;
          }

          array_push($data['sections'], $itemS);
        }
        break;

      default:
        $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();
        $strSectionIds = $libraryTest->section_ids;
        $arrSectionIds = explode(",", $strSectionIds);
        $lstSection = DB::table('section')->whereIn('id', $arrSectionIds)->orderBy('id', 'ASC')->get();

        foreach ($lstSection as $section) {
          $sectionId = $section->id;
          $itemS = [
            'section_id' => $section->id,
            'section_name' => $section->section_name,
            'passage' => $section->passage,
            'questions' => []
          ];

          $lstSectionQuestion = DB::table('section_question')->join('library_question', 'library_question.id', '=', 'section_question.question_id')->where('section_id', $sectionId)->orderBy('question_order', 'ASC')->get();

          $keyQuestion = 0;
          foreach ($lstSectionQuestion as $questionVal) {
            switch ($questionVal->main_type) {
              case config('trial_test.main_type.sort'):
                $arrMerged = array_merge(explode("|", $questionVal->correct_answer), explode("|", $questionVal->incorrect_answer));
                shuffle($arrMerged);
                $arrShuffleAnswer = [];
                foreach ($arrMerged as $key => $value) {
                  array_push($arrShuffleAnswer, [
                    'answer' => $value,
                    'answer_play_audio' => 0
                  ]);
                }

                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'content_main_audio' => $questionVal->content_main_audio,
                  'content_main_picture' => $questionVal->content_main_picture,
                  'picture_bellow_text' => $questionVal->picture_bellow_text,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'voice_for_content_main' => $questionVal->voice_for_content_main,
                  'voice_for_answer' => $questionVal->voice_for_answer,
                  'correct_answer' => explode("|", $questionVal->correct_answer),
                  'incorrect_answer' => explode("|", $questionVal->incorrect_answer),
                  'shuffle_answer' => $arrShuffleAnswer,
                  'category' => $questionVal->category,
                  'title_play_audio' => 0,
                  'content_play_audio' => 0,
                  'contents' => []
                ];
                if ($questionVal->content_main_audio) {
                  $itemQ['play_audio'] = 0;
                }

                $contentMainText = $questionVal->content_main_text;
                if ($questionVal->category == config('trial_test.category.writing')) {
                  $countChartInContentMain = substr_count($contentMainText, "??");
                  $template = $contentMainText;

                  for ($i = 0; $i < $countChartInContentMain; $i++) {
                    $replaceTemplate = $this->str_replace_first("??", ' <div id="question-' . ($questionVal->id) . '-drag' . ($i + 1) . '_answer" class="container_drag mt-1 style_answer" draggable="true"> </div> ', $template);
                    $template = $replaceTemplate;
                  }

                  $template = str_replace("|", " ", $template);

                  $itemC = [
                    'template' => $template,
                    'complete_answer' => null,
                    'is_correct_answer' => null
                  ];
                  array_push($itemQ['contents'], $itemC);
                } else {
                  $countChartInContentMain = substr_count($contentMainText, "??");
                  $template = $contentMainText;

                  for ($i = 0; $i < $countChartInContentMain; $i++) {
                    $template = $this->str_replace_first("??", ' <div id="question-' . ($questionVal->id) . '-drag' . ($i + 1) . '_answer" class="container_drag mt-1 style_answer" draggable="true"> </div> ', $template);
                  }

                  $template = str_replace("|", " ", $template);
                  $itemC = [
                    'template' => $template
                  ];
                  array_push($itemQ['contents'], $itemC);
                }

                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
              case config('trial_test.main_type.q_and_a'):
                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'voice_for_content_main' => $questionVal->voice_for_content_main,
                  'content_main_text' => $questionVal->content_main_text,
                  'content_main_picture' => $questionVal->content_main_picture,
                  'content_main_audio' => $questionVal->content_main_audio,
                  'picture_bellow_text' => $questionVal->picture_bellow_text,
                  'title_play_audio' => 0,
                  'content_play_audio' => 0,
                  'sub_questions' => []
                ];
                if ($questionVal->content_main_audio) {
                  $itemQ['play_audio'] = 0;
                }

                $libraryQuestionQnaDetail = DB::table('library_question_qna_detail')->where('library_question_id', $questionVal->id)->orderBy('order', 'ASC')->get();
                foreach ($libraryQuestionQnaDetail as $qnaDetailKey => $qnaDetailVal) {
                  $qnaCorrectAnswer = explode("|", $qnaDetailVal->correct_answer);
                  $qnaInCorrectAnswer = explode("|", $qnaDetailVal->incorrect_answer);
                  $arrShuffle = [];
                  $mergeAnswer = array_merge($qnaCorrectAnswer, $qnaInCorrectAnswer);
                  shuffle($mergeAnswer);

                  foreach ($mergeAnswer as $keyAnswer => $answer) {
                    if ($answer) {
                      $itemShuffle = [
                        'answer' => $answer,
                        'answer_play_audio' => 0,
                      ];

                      array_push($arrShuffle, $itemShuffle);
                    }
                  }

                  $itemSubQ = [
                    'sub_question' => $qnaDetailVal->sub_question,
                    'voice_for_sub_question' => $qnaDetailVal->voice_for_sub_question,
                    'voice_for_answer' => $qnaDetailVal->voice_for_answer,
                    'subQ_play_audio' => 0,
                    'correct_answer' => $qnaCorrectAnswer,
                    'incorrect_answer' => $qnaInCorrectAnswer,
                    'shuffle_answer' => $arrShuffle,
                    'countChoose' => 0
                  ];
                  array_push($itemQ['sub_questions'], $itemSubQ);
                };

                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
              case config('trial_test.main_type.matching'):
                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'title_play_audio' => 0,
                  'row_1' => [],
                  'row_2' => [],
                  'shuffle_row_1' => [],
                  'shuffle_row_2' => []
                ];

                $libraryQuestionMatchingDetail = DB::table('library_question_matching_detail')->where('library_question_id', $questionVal->id)->orderBy('order', 'ASC')->get();
                foreach ($libraryQuestionMatchingDetail as $key => $value) {
                  $itemRow1 = [
                    'content_text_a' => $value->content_text_a,
                    'picture_url_a' => $value->picture_url_a,
                    'voice_for_content_text_a' => $value->voice_for_content_text_a,
                    'play_audio' => 0
                  ];
                  array_push($itemQ['row_1'], $itemRow1);

                  $itemRow2 = [
                    'content_text_b' => $value->content_text_b,
                    'picture_url_b' => $value->picture_url_b,
                    'audio_url' => $value->audio_url
                  ];
                  // if ($value->audio_url) {
                  $itemRow2['play_audio'] = 0;
                  // }
                  array_push($itemQ['row_2'], $itemRow2);
                }

                $itemQ['shuffle_row_1'] = $itemQ['row_1'];
                $itemQ['shuffle_row_2'] = $itemQ['row_2'];
                shuffle($itemQ['shuffle_row_1']);
                shuffle($itemQ['shuffle_row_2']);
                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
              case config('trial_test.main_type.fill_input'):
                $arrContentMain = explode("\n", $questionVal->content_main_text);
                $itemQ = [
                  'main_type' => $questionVal->main_type,
                  'content_main_picture' => $questionVal->content_main_picture,
                  'content_main_text' => $questionVal->content_main_text,
                  'content_main_audio' => $questionVal->content_main_audio,
                  'question_id' => $questionVal->id,
                  'title' => $questionVal->title,
                  'audio_title' => $questionVal->audio_title,
                  'title_play_audio_with_url' => 0,
                  'title_play_audio' => 0,
                  'voice_for_title' => $questionVal->voice_for_title,
                  'arr_content_main' => $arrContentMain,
                  'picture_bellow_text' => $questionVal->picture_bellow_text,
                  'contents' => []
                ];
                if ($questionVal->content_main_audio) {
                  $itemQ['play_audio'] = 0;
                }
                $idxTemplate = 0;
                foreach ($arrContentMain as $key => $value) {
                  $runWhile = true;
                  $contentMain = $arrContentMain[$key];
                  $template = $contentMain;
                  $idxCharInContent = 0;
                  while ($runWhile) {
                    if ($idxCharInContent >= substr_count($contentMain, "??")) {
                      $runWhile = false;
                      break;
                    }

                    $replaceTemplate = $this->str_replace_first("??", ' <span id="question-' . ($questionVal->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class="custom_fill_input" contenteditable></span> ', $template);
                    if ($replaceTemplate) {
                      $template = $replaceTemplate;
                      $idxTemplate += 1;
                    } else {
                      $runWhile = false;
                    }

                    $idxCharInContent += 1;
                  }

                  $template = str_replace("|", " ", $template);

                  $itemC = [
                    'template' => $template
                  ];
                  array_push($itemQ['contents'], $itemC);
                }

                array_push($itemS['questions'], $itemQ);
                $keyQuestion += 1;
                break;
            }
          }

          array_push($data['sections'], $itemS);
        }
        break;
    }

    return [
      'code' => '10000',
      'test_time' => $remainTime,
      'data' => $data,
      'message' => 'success'
    ];
  }

  public function getTestResults(Request $req)
  {
    $testCode = $req->test_code;
    $studentTestResult = StudentTestResult::where('code', $testCode)
      ->orderBy('created_at', 'DESC')->first();
    $libraryTest = DB::table('library_test')->where('id', $studentTestResult->library_test_id)->first();
    $topicTestType = $libraryTest->test_type;
    if ($topicTestType == config('trial_test.topic.test_type.ielts_writing')) {
      return $this->getIeltsWritingTestResults($req);
    } else if (
      $topicTestType == config('trial_test.topic.test_type.ielts_listening') ||
      $topicTestType == config('trial_test.topic.test_type.ielts_reading')
    ) {
      return $this->getIeltsReadingTestResults($req);
    }

    return $this->getTrialTestResults($req);
  }

  public function getIeltsWritingTestResults(Request $req)
  {
    $data = null;
    $remainTime = null;
    $testCode = $req->test_code;
    $studentTestResult = StudentTestResult::where('code', $testCode)->first();
    if (!$studentTestResult->end_at) {
      Log::error("Did not complete the test");
      return [
        'code' => '10001',
        'message' => 'Chưa hoàn thành bài kiểm tra'
      ];
    }

    $idStudentTestResult = $studentTestResult->id;
    $idTopic = $studentTestResult->library_test_id;
    $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();
    $topicTestType = $libraryTest->test_type;
    $data = [
      'id_student_test_result' => $idStudentTestResult,
      'isResult' => true,
      'topic_test_type' => $topicTestType,
      'sections' => []
    ];

    $strSectionIds = $libraryTest->section_ids;
    $arrSectionIds = explode(",", $strSectionIds);
    $lstSection = DB::table('section')->whereIn('id', $arrSectionIds)->orderBy('id', 'ASC')->get();

    foreach ($lstSection as $section) {
      $sectionId = $section->id;
      $itemS = [
        'section_id' => $section->id,
        'section_name' => $section->section_name,
        'passage' => $section->passage,
        'questions' => []
      ];

      $lstSectionQuestion = DB::table('section_question')->join('library_question', 'library_question.id', '=', 'section_question.question_id')->where('section_id', $sectionId)->orderBy('question_order', 'ASC')->get();
      foreach ($lstSectionQuestion as $questionVal) {
        $val = DB::table('student_test_question_result')->where('student_test_result_id', $idStudentTestResult)->where('question_id', $questionVal->question_id)->first();
        if($val) {
            $dataQuestion = DB::table('library_question')->where('id', $val->question_id)->first();

            $itemQ = [
                'main_type' => $dataQuestion->main_type,
                'content_main_picture' => $dataQuestion->content_main_picture,
                'content_main_text' => $dataQuestion->content_main_text,
                'content_main_audio' => $dataQuestion->content_main_audio,
                'question_id' => $dataQuestion->id,
                'title' => $dataQuestion->title,
                'audio_title' => $dataQuestion->audio_title,
                'title_play_audio_with_url' => 0,
                'title_play_audio' => 0,
                'voice_for_title' => $dataQuestion->voice_for_title,
                'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                'word_minimum' => $dataQuestion->word_minimum,
                'answer' => $val->answer
            ];

            array_push($itemS['questions'], $itemQ);
        }
      }

      array_push($data['sections'], $itemS);
    }

    return [
      'code' => '10000',
      'test_time' => $remainTime,
      'data' => $data,
      'message' => 'success'
    ];
  }

  public function getIeltsReadingTestResults(Request $req)
  {
    $data = null;
    $remainTime = null;
    $testCode = $req->test_code;

    $studentTestResult = StudentTestResult::where('code', $testCode)->first();
    if (!$studentTestResult->end_at) {
      Log::error("Did not complete the test");
      return [
        'code' => '10001',
        'message' => 'Chưa hoàn thành bài kiểm tra'
      ];
    }

    $idStudentTestResult = $studentTestResult->id;
    $idTopic = $studentTestResult->library_test_id;
    $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();
    $topicTestType = $libraryTest->test_type;
    $data = [
      'id_student_test_result' => $idStudentTestResult,
      'isResult' => true,
      'topic_test_type' => $topicTestType,
      'sections' => []
    ];

    $strSectionIds = $libraryTest->section_ids;
    $arrSectionIds = explode(",", $strSectionIds);
    $lstSection = DB::table('section')->whereIn('id', $arrSectionIds)->orderBy('id', 'ASC')->get();

    foreach ($lstSection as $section) {
      $sectionId = $section->id;
      $itemS = [
        'section_id' => $section->id,
        'section_name' => $section->section_name,
        'passage' => $section->passage,
        'audio' => $section->audio,
        'questions' => []
      ];

      if ($section->audio) {
        $itemS['play_audio'] = 0;
      }

      $lstSectionQuestion = DB::table('section_question')->join('library_question', 'library_question.id', '=', 'section_question.question_id')->where('section_id', $sectionId)->orderBy('question_order', 'ASC')->get();
      $keyQuestion = 0;
      foreach ($lstSectionQuestion as $questionVal) {
        $dataQuestion = DB::table('library_question')->where('id', $questionVal->question_id)->first();
        $libraryQuestionQnaDetail = DB::table('library_question_qna_detail')->where('library_question_id', $questionVal->question_id)->orderBy('order', 'ASC')->get();
        if (count($libraryQuestionQnaDetail) > 1 || $dataQuestion->main_type == config('trial_test.main_type.matching')) {
          continue;
        }

        $val = DB::table('student_test_question_result')->where('student_test_result_id', $idStudentTestResult)->where('question_id', $questionVal->question_id)->first();
        if($val) {
            $valAnswerDecode = json_decode($val->answer);
            switch ($dataQuestion->main_type) {
                case config('trial_test.main_type.sort'):
                    $correctAnswer = explode("|", $dataQuestion->correct_answer);
                    $arrMerged = array_merge($correctAnswer, explode("|", $dataQuestion->incorrect_answer));
                    shuffle($arrMerged);

                    $arrShuffleAnswer = [];
                    foreach ($arrMerged as $key => $value) {
                        array_push($arrShuffleAnswer, [
                            'answer' => $value,
                            'answer_play_audio' => 0
                        ]);
                    }

                    $studentAnswers = [];
                    foreach ($valAnswerDecode as $key => $value) {
                        array_push($studentAnswers, [
                            'answer' => $value,
                            'answer_play_audio' => 0
                        ]);
                    }

                    $itemQ = [
                        'main_type' => $dataQuestion->main_type,
                        'content_main_audio' => $dataQuestion->content_main_audio,
                        'content_main_picture' => $dataQuestion->content_main_picture,
                        'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                        'question_id' => $dataQuestion->id,
                        'title' => $dataQuestion->title,
                        'audio_title' => $dataQuestion->audio_title,
                        'title_play_audio_with_url' => 0,
                        'voice_for_title' => $dataQuestion->voice_for_title,
                        'voice_for_content_main' => $dataQuestion->voice_for_content_main,
                        'voice_for_answer' => $dataQuestion->voice_for_answer,
                        'correct_answer' => $correctAnswer,
                        'incorrect_answer' => explode("|", $dataQuestion->incorrect_answer),
                        'shuffle_answer' => $arrShuffleAnswer,
                        'student_answers' => $studentAnswers,
                        'category' => $dataQuestion->category,
                        'title_play_audio' => 0,
                        'content_play_audio' => 0,
                        'answer_play_audio' => 0,
                        'contents' => []
                    ];
                    if ($dataQuestion->content_main_audio) {
                        $itemQ['play_audio'] = 0;
                    }

                    $keyQ = $dataQuestion->id;
                    $contentMainText = $dataQuestion->content_main_text;
                    if ($dataQuestion->category == config('trial_test.category.writing')) {
                        $isCorrectAnswer = true;
                        if (!substr_count($contentMainText, "??")) {
                            $isCorrectAnswer = 'none';
                        }

                        $countChartInContentMain = substr_count($contentMainText, "??");
                        $template = $contentMainText;
                        $completeAnswer = $contentMainText;

                        for ($i = 0; $i < $countChartInContentMain; $i++) {
                            $keyAnswer = $i + 1;
                            $iconSpeaker = '';
                            if ($dataQuestion->voice_for_answer == 1 && !empty($valAnswerDecode[$i])) {
                                $iconSpeaker .= '<img class="ml-1 icon_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: inline-block" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker.png" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                                $iconSpeaker .= '<img class="ml-1 icon_gif_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: none" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker_animated.gif" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                            }

                            $replaceTemplate = $this->str_replace_first("??", ' <div class="mt-1 style_answer pb-0 pt-18">' . (!empty($valAnswerDecode[$i]) ? $valAnswerDecode[$i] : ' ') . $iconSpeaker . '</div> ', $template);
                            $replaceCompleteAnswer = $this->str_replace_first("??", trim($correctAnswer[$i]), $completeAnswer);
                            $template = $replaceTemplate;
                            $completeAnswer = $replaceCompleteAnswer;

                            if ($isCorrectAnswer) {
                                $isCorrectAnswer = trim(strip_tags($correctAnswer[$i])) == trim(strip_tags($valAnswerDecode[$i]));
                            }
                        }

                        $template = str_replace("|", " ", $template);
                        $completeAnswer = str_replace("|", " ", $completeAnswer);

                        $itemC = [
                            'template' => $template,
                            'complete_answer' => $completeAnswer,
                            'is_correct_answer' => $isCorrectAnswer
                        ];
                        array_push($itemQ['contents'], $itemC);
                    } else {
                        $countChartInContentMain = substr_count($contentMainText, "??");
                        $template = $contentMainText;

                        for ($i = 0; $i < $countChartInContentMain; $i++) {
                            $isCorrectAnswer = trim(strip_tags($correctAnswer[$i])) == trim(strip_tags($valAnswerDecode[$i]));
                            $iconResult = '';
                            $strCorrectAnswer = '';
                            if ($isCorrectAnswer) {
                                $iconResult = ' <i style="font-size: 50px; color: #7BBB44;" class="fas fa-check-circle"></i> ';
                                $strCorrectAnswer = '';
                            } else {
                                $iconResult = ' <i style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i> ';
                                $strCorrectAnswer = ' <div class="mt-1 style_correct_answer pb-0 pt-18"> Correct: ' . $correctAnswer[$i] . '</div> ';
                            }

                            $keyAnswer = $i + 1;
                            $iconSpeaker = '';
                            if ($dataQuestion->voice_for_answer == 1 && !empty($valAnswerDecode[$i])) {
                                $iconSpeaker .= '<img class="ml-1 icon_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: inline-block" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker.png" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                                $iconSpeaker .= '<img class="ml-1 icon_gif_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: none" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker_animated.gif" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                            }

                            $template = $this->str_replace_first("??", ' <div class="d-inline-flex">' . $iconResult . ' <div class="mt-1 style_answer pb-0 pt-18">' . (!empty($valAnswerDecode[$i]) ? $valAnswerDecode[$i] : ' ') . $iconSpeaker . '</div> ' . $strCorrectAnswer . '</div> ', $template);
                        }

                        $template = str_replace("|", " ", $template);
                        $itemC = [
                            'template' => $template
                        ];
                        array_push($itemQ['contents'], $itemC);
                    }

                    array_push($itemS['questions'], $itemQ);
                    $keyQuestion += 1;
                    break;

                case config('trial_test.main_type.q_and_a'):
                    $itemQ = [
                        'main_type' => $dataQuestion->main_type,
                        'question_id' => $dataQuestion->id,
                        'title' => $dataQuestion->title,
                        'audio_title' => $dataQuestion->audio_title,
                        'title_play_audio_with_url' => 0,
                        'voice_for_title' => $dataQuestion->voice_for_title,
                        'voice_for_content_main' => $dataQuestion->voice_for_content_main,
                        'content_main_text' => $dataQuestion->content_main_text,
                        'content_main_picture' => $dataQuestion->content_main_picture,
                        'content_main_audio' => $dataQuestion->content_main_audio,
                        'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                        'title_play_audio' => 0,
                        'content_play_audio' => 0,
                        'sub_questions' => []
                    ];

                    if ($dataQuestion->content_main_audio) {
                        $itemQ['play_audio'] = 0;
                    }

                    // $libraryQuestionQnaDetail = DB::table('library_question_qna_detail')->where('library_question_id', $dataQuestion->id)->orderBy('order', 'ASC')->get();
                    foreach ($libraryQuestionQnaDetail as $qnaDetailKey => $qnaDetailVal) {
                        $qnaCorrectAnswer = array_map('trim', array_map('strip_tags', explode("|", $qnaDetailVal->correct_answer)));
                        $qnaInCorrectAnswer = array_map('trim', array_map('strip_tags', explode("|", $qnaDetailVal->incorrect_answer)));

                        $arrShuffle = [];
                        $mergeAnswer = array_merge($qnaCorrectAnswer, $qnaInCorrectAnswer);
                        shuffle($mergeAnswer);
                        $resultStatus = false;
                        foreach ($mergeAnswer as $keyAnswer => $answer) {
                            if ($answer) {
                                $isCorrectAnswer = 'none';
                                $answerToChoose = 'none';

                                // if (trim(strip_tags($valAnswerDecode[$qnaDetailKey])) == trim(strip_tags($qnaDetailVal->correct_answer)) && trim(strip_tags($qnaDetailVal->correct_answer)) == trim(strip_tags($answer))) {
                                //   $resultStatus = true;
                                //   $isCorrectAnswer = true;
                                // } else {
                                //   if (trim(strip_tags($valAnswerDecode[$qnaDetailKey])) == trim(strip_tags($answer))) {
                                //     $isCorrectAnswer = false;
                                //   }
                                //   if (trim(strip_tags($qnaDetailVal->correct_answer)) == trim(strip_tags($answer))) {
                                //     $answerToChoose = true;
                                //   }
                                // }

                                if (in_array(trim($answer), $valAnswerDecode[$qnaDetailKey]) && in_array(trim($answer), $qnaCorrectAnswer)) {
                                    $resultStatus = true;
                                    $isCorrectAnswer = true;
                                } else {
                                    if (in_array(trim($answer), $valAnswerDecode[$qnaDetailKey])) {
                                        $isCorrectAnswer = false;
                                    }
                                    if (in_array(trim($answer), $qnaCorrectAnswer)) {
                                        $answerToChoose = true;
                                    }
                                }

                                $itemShuffle = [
                                    'answer' => $answer,
                                    'answer_play_audio' => 0,
                                    'is_correct_answer' => $isCorrectAnswer,
                                    'answer_to_choose' => $answerToChoose
                                ];

                                array_push($arrShuffle, $itemShuffle);
                            }
                        }
                        $itemSubQ = [
                            'sub_question' => $qnaDetailVal->sub_question,
                            'voice_for_sub_question' => $qnaDetailVal->voice_for_sub_question,
                            'voice_for_answer' => $qnaDetailVal->voice_for_answer,
                            'subQ_play_audio' => 0,
                            'answer_play_audio' => 0,
                            'correct_answer' => explode("|", $qnaDetailVal->correct_answer),
                            'incorrect_answer' => explode("|", $qnaDetailVal->incorrect_answer),
                            'result_status' => $resultStatus,
                            'shuffle_answer' => $arrShuffle
                        ];
                        array_push($itemQ['sub_questions'], $itemSubQ);
                    };

                    array_push($itemS['questions'], $itemQ);
                    $keyQuestion += 1;
                    break;

                case config('trial_test.main_type.fill_input'):
                    $arrContentMain = explode("\n", $dataQuestion->content_main_text);
                    $itemQ = [
                        'main_type' => $dataQuestion->main_type,
                        'content_main_picture' => $dataQuestion->content_main_picture,
                        'content_main_text' => $dataQuestion->content_main_text,
                        'content_main_audio' => $dataQuestion->content_main_audio,
                        'question_id' => $dataQuestion->id,
                        'title' => $dataQuestion->title,
                        'audio_title' => $dataQuestion->audio_title,
                        'title_play_audio_with_url' => 0,
                        'title_play_audio' => 0,
                        'voice_for_title' => $dataQuestion->voice_for_title,
                        'arr_content_main' => $arrContentMain,
                        'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                        'contents' => []
                    ];

                    if ($dataQuestion->content_main_audio) {
                        $itemQ['play_audio'] = 0;
                    }

                    $idxTemplate = 0;
                    $arrCorrectAnswer = json_decode($dataQuestion->correct_answer);

                    foreach ($arrContentMain as $key => $value) {
                        $runWhile = true;
                        $contentMain = $arrContentMain[$key];
                        $template = $contentMain;
                        $completeAnswer = $contentMain;
                        $idxCharInContent = 0;
                        $isCorrectAnswer = true;
                        while ($runWhile) {
                            if ($idxCharInContent >= substr_count($contentMain, "??")) {
                                $runWhile = false;
                                break;
                            }

                            $srtAnswer = '';
                            if (isset($valAnswerDecode[$key][$idxCharInContent])) {
                                $srtAnswer = implode(" ", $valAnswerDecode[$key][$idxCharInContent]);
                            }
                            $arrCorrectAnswerSub = [];
                            $arrCorrectAnswerSubLowercase = [];
                            if (isset($arrCorrectAnswer[$idxTemplate])) {
                                $arrCorrectAnswerSub = explode("|", $arrCorrectAnswer[$idxTemplate]);
                                foreach ($arrCorrectAnswerSub as $keyAnswerSub => $valAnswerSub) {
                                    array_push($arrCorrectAnswerSubLowercase, trim(strtolower(strip_tags($valAnswerSub))));
                                }
                            }

                            $isCorrectAnswerSub = !empty(strip_tags($srtAnswer)) && !empty($arrCorrectAnswerSubLowercase) && in_array(trim(strtolower(strip_tags($srtAnswer))), $arrCorrectAnswerSubLowercase) ? true : false;

                            if ($isCorrectAnswer) {
                                $isCorrectAnswer = $isCorrectAnswerSub;
                            }

                            $iconResult = '';
                            $styleAnswer = '';
                            if ($isCorrectAnswerSub) {
                                $styleAnswer = 'style_typing_correct_answer';
                                $iconResult = ' <i style="display: inline-block; margin-left: 5px; margin-right: 5px; font-size: 30px; color: #7BBB44;" class="fas fa-check-circle"></i> ';
                            } else {
                                $styleAnswer = 'style_typing_incorrect_answer';
                                $iconResult = ' <i style="display: inline-block; margin-left: 5px; margin-right: 5px; font-size: 30px; color: #FF0000;" class="far fa-times-circle"></i> ';
                            }
                            Log::debug(">>>>> iconResult: ");
                            Log::debug($iconResult);
                            $replaceTemplate = $this->str_replace_first("??", $iconResult . ' <span id="question-' . ($dataQuestion->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class=" ' . $styleAnswer . ' "> ' . $srtAnswer . '</span> ', $template);
                            $replaceCompleteAnswer = $this->str_replace_first("??", (!empty($arrCorrectAnswerSub) ? $arrCorrectAnswerSub[0] : ''), $completeAnswer);
                            if ($replaceTemplate) {
                                $template = $replaceTemplate;
                                $completeAnswer = $replaceCompleteAnswer;
                                $idxTemplate += 1;
                            } else {
                                $runWhile = false;
                            }

                            $idxCharInContent += 1;
                        }

                        $template = str_replace("|", " ", $template);
                        $completeAnswer = str_replace("|", " ", $completeAnswer);

                        $itemC = [
                            'template' => $template,
                            'complete_answer' => $completeAnswer,
                            'is_correct_answer' => $isCorrectAnswer
                        ];
                        array_push($itemQ['contents'], $itemC);
                    }


                    array_push($itemS['questions'], $itemQ);
                    $keyQuestion += 1;
                    break;

                case config('trial_test.main_type.drop_down'):
                    $arrContentMain = explode("\n", $dataQuestion->content_main_text);
                    $lstOption = json_decode($dataQuestion->dropdown_list);

                    $itemQ = [
                        'main_type' => $dataQuestion->main_type,
                        'content_main_picture' => $dataQuestion->content_main_picture,
                        'content_main_text' => $dataQuestion->content_main_text,
                        'content_main_audio' => $dataQuestion->content_main_audio,
                        'question_id' => $dataQuestion->id,
                        'title' => $dataQuestion->title,
                        'audio_title' => $dataQuestion->audio_title,
                        'title_play_audio_with_url' => 0,
                        'title_play_audio' => 0,
                        'voice_for_title' => $dataQuestion->voice_for_title,
                        'arr_content_main' => $arrContentMain,
                        'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                        'options' => $lstOption,
                        'flag_show_dropdown' => $dataQuestion->flag_show_dropdown,
                        'contents' => []
                    ];

                    if ($dataQuestion->content_main_audio) {
                        $itemQ['play_audio'] = 0;
                    }

                    $idxTemplate = 0;
                    $arrCorrectAnswer = explode("|", $dataQuestion->correct_answer);

                    foreach ($arrContentMain as $key => $value) {
                        $runWhile = true;
                        $contentMain = $arrContentMain[$key];
                        $template = $contentMain;
                        $completeAnswer = $contentMain;
                        $idxCharInContent = 0;
                        $isCorrectAnswer = true;
                        while ($runWhile) {
                            if ($idxCharInContent >= substr_count($contentMain, "??")) {
                                $runWhile = false;
                                break;
                            }

                            $srtAnswer = '';
                            $optionVal = null;
                            if (isset($valAnswerDecode[$key][$idxCharInContent])) {
                                $optionVal = implode(" ", $valAnswerDecode[$key][$idxCharInContent]);
                                $lstOption = json_decode($dataQuestion->dropdown_list);
                                foreach ($lstOption as $keyOption => $option) {
                                    if ($optionVal == $option->value) {
                                        $srtAnswer = $option->content;
                                        break;
                                    }
                                }
                            }
                            $strCorrectAnswerSub = '';
                            $correctAnswerSub = null;
                            if (isset($arrCorrectAnswer[$idxTemplate])) {
                                $correctAnswerSub = $arrCorrectAnswer[$idxTemplate];
                                foreach ($lstOption as $keyOption => $option) {
                                    if ($correctAnswerSub == $option->value) {
                                        $strCorrectAnswerSub = $option->content;
                                        break;
                                    }
                                }
                            }

                            $isCorrectAnswerSub = !empty($optionVal) && !empty($correctAnswerSub) && trim($optionVal) == trim($correctAnswerSub) ? true : false;

                            if ($isCorrectAnswer) {
                                $isCorrectAnswer = $isCorrectAnswerSub;
                            }

                            $iconResult = '';
                            $styleAnswer = '';
                            if ($isCorrectAnswerSub) {
                                $styleAnswer = 'style_typing_correct_answer';
                                $iconResult = ' <i style="display: inline-block; margin-left: 5px; margin-right: 5px; font-size: 30px; color: #7BBB44;" class="fas fa-check-circle"></i> ';
                            } else {
                                $styleAnswer = 'style_typing_incorrect_answer';
                                $iconResult = ' <i style="display: inline-block; margin-left: 5px; margin-right: 5px; font-size: 30px; color: #FF0000;" class="far fa-times-circle"></i> ';
                            }
                            Log::debug(">>>>> iconResult: ");
                            Log::debug($iconResult);
                            $replaceTemplate = $this->str_replace_first("??", $iconResult . ' <span id="question-' . ($dataQuestion->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class=" ' . $styleAnswer . ' "> ' . $srtAnswer . '</span> ', $template);
                            $replaceCompleteAnswer = $this->str_replace_first("??", (!empty($strCorrectAnswerSub) ? $strCorrectAnswerSub : ''), $completeAnswer);
                            if ($replaceTemplate) {
                                $template = $replaceTemplate;
                                $completeAnswer = $replaceCompleteAnswer;
                                $idxTemplate += 1;
                            } else {
                                $runWhile = false;
                            }

                            $idxCharInContent += 1;
                        }

                        $template = str_replace("|", " ", $template);
                        $completeAnswer = str_replace("|", " ", $completeAnswer);

                        $itemC = [
                            'template' => $template,
                            'complete_answer' => $completeAnswer,
                            'is_correct_answer' => $isCorrectAnswer
                        ];
                        array_push($itemQ['contents'], $itemC);
                    }


                    array_push($itemS['questions'], $itemQ);
                    $keyQuestion += 1;
                    break;
            }
        }
      }

      array_push($data['sections'], $itemS);
    }

    return [
      'code' => '10000',
      'test_time' => $remainTime,
      'data' => $data,
      'message' => 'success'
    ];
  }

  public function getTrialTestResults(Request $req)
  {
    $data = null;
    $remainTime = null;
    $testCode = $req->test_code;

    $studentTestResult = StudentTestResult::where('code', $testCode)->first();
    if (!$studentTestResult->end_at) {
      Log::error("Did not complete the test");
      return [
        'code' => '10001',
        'message' => 'Chưa hoàn thành bài kiểm tra'
      ];
    }

    $idStudentTestResult = $studentTestResult->id;
    $idTopic = $studentTestResult->library_test_id;
    $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();
    $topicTestType = $libraryTest->test_type;
    $data = [
      'id_student_test_result' => $idStudentTestResult,
      'isResult' => true,
      'topic_test_type' => $topicTestType,
      'sections' => []
    ];

    $strSectionIds = $libraryTest->section_ids;
    $arrSectionIds = explode(",", $strSectionIds);
    $lstSection = DB::table('section')->whereIn('id', $arrSectionIds)->orderBy('id', 'ASC')->get();

    foreach ($lstSection as $section) {
      $sectionId = $section->id;
      $itemS = [
        'section_id' => $section->id,
        'section_name' => $section->section_name,
        'passage' => $section->passage,
        'questions' => []
      ];

      $lstSectionQuestion = DB::table('section_question')->join('library_question', 'library_question.id', '=', 'section_question.question_id')->where('section_id', $sectionId)->orderBy('question_order', 'ASC')->get();
      $keyQuestion = 0;
      foreach ($lstSectionQuestion as $questionVal) {
        $val = DB::table('student_test_question_result')->where('student_test_result_id', $idStudentTestResult)->where('question_id', $questionVal->question_id)->first();
        if($val) {
            $valAnswerDecode = json_decode($val->answer);
            $dataQuestion = DB::table('library_question')->where('id', $val->question_id)->first();
            if($dataQuestion) {
                switch ($dataQuestion->main_type) {
                    case config('trial_test.main_type.sort'):
                        $correctAnswer = explode("|", $dataQuestion->correct_answer);
                        $arrMerged = array_merge($correctAnswer, explode("|", $dataQuestion->incorrect_answer));
                        shuffle($arrMerged);

                        $arrShuffleAnswer = [];
                        foreach ($arrMerged as $key => $value) {
                            array_push($arrShuffleAnswer, [
                                'answer' => $value,
                                'answer_play_audio' => 0
                            ]);
                        }

                        $studentAnswers = [];
                        foreach ($valAnswerDecode as $key => $value) {
                            array_push($studentAnswers, [
                                'answer' => $value,
                                'answer_play_audio' => 0
                            ]);
                        }

                        $itemQ = [
                            'main_type' => $dataQuestion->main_type,
                            'content_main_audio' => $dataQuestion->content_main_audio,
                            'content_main_picture' => $dataQuestion->content_main_picture,
                            'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                            'question_id' => $dataQuestion->id,
                            'title' => $dataQuestion->title,
                            'audio_title' => $dataQuestion->audio_title,
                            'title_play_audio_with_url' => 0,
                            'voice_for_title' => $dataQuestion->voice_for_title,
                            'voice_for_content_main' => $dataQuestion->voice_for_content_main,
                            'voice_for_answer' => $dataQuestion->voice_for_answer,
                            'correct_answer' => $correctAnswer,
                            'incorrect_answer' => explode("|", $dataQuestion->incorrect_answer),
                            'shuffle_answer' => $arrShuffleAnswer,
                            'student_answers' => $studentAnswers,
                            'category' => $dataQuestion->category,
                            'title_play_audio' => 0,
                            'content_play_audio' => 0,
                            'answer_play_audio' => 0,
                            'contents' => []
                        ];
                        if ($dataQuestion->content_main_audio) {
                            $itemQ['play_audio'] = 0;
                        }

                        $keyQ = $dataQuestion->id;
                        $contentMainText = $dataQuestion->content_main_text;
                        if ($dataQuestion->category == config('trial_test.category.writing')) {
                            $isCorrectAnswer = true;
                            if (!substr_count($contentMainText, "??")) {
                                $isCorrectAnswer = 'none';
                            }

                            $countChartInContentMain = substr_count($contentMainText, "??");
                            $template = $contentMainText;
                            $completeAnswer = $contentMainText;

                            for ($i = 0; $i < $countChartInContentMain; $i++) {
                                $keyAnswer = $i + 1;
                                $iconSpeaker = '';
                                if ($dataQuestion->voice_for_answer == 1 && !empty($valAnswerDecode[$i])) {
                                    $iconSpeaker .= '<img class="ml-1 icon_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: inline-block" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker.png" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                                    $iconSpeaker .= '<img class="ml-1 icon_gif_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: none" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker_animated.gif" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                                }

                                $replaceTemplate = $this->str_replace_first("??", ' <div class="mt-1 style_answer pb-0 pt-18">' . (!empty($valAnswerDecode[$i]) ? $valAnswerDecode[$i] : ' ') . $iconSpeaker . '</div> ', $template);
                                $replaceCompleteAnswer = $this->str_replace_first("??", trim($correctAnswer[$i]), $completeAnswer);
                                $template = $replaceTemplate;
                                $completeAnswer = $replaceCompleteAnswer;

                                if ($isCorrectAnswer) {
                                    $isCorrectAnswer = trim(strip_tags($correctAnswer[$i])) == trim(strip_tags($valAnswerDecode[$i]));
                                }
                            }

                            $template = str_replace("|", " ", $template);
                            $completeAnswer = str_replace("|", " ", $completeAnswer);

                            $itemC = [
                                'template' => $template,
                                'complete_answer' => $completeAnswer,
                                'is_correct_answer' => $isCorrectAnswer
                            ];
                            array_push($itemQ['contents'], $itemC);
                        } else {
                            $countChartInContentMain = substr_count($contentMainText, "??");
                            $template = $contentMainText;

                            for ($i = 0; $i < $countChartInContentMain; $i++) {
                                $isCorrectAnswer = trim(strip_tags($correctAnswer[$i])) == trim(strip_tags($valAnswerDecode[$i]));
                                $iconResult = '';
                                $strCorrectAnswer = '';
                                if ($isCorrectAnswer) {
                                    $iconResult = ' <i style="font-size: 50px; color: #7BBB44;" class="fas fa-check-circle"></i> ';
                                    $strCorrectAnswer = '';
                                } else {
                                    $iconResult = ' <i style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i> ';
                                    $strCorrectAnswer = ' <div class="mt-1 style_correct_answer pb-0 pt-18"> Correct: ' . $correctAnswer[$i] . '</div> ';
                                }

                                $keyAnswer = $i + 1;
                                $iconSpeaker = '';
                                if ($dataQuestion->voice_for_answer == 1 && !empty($valAnswerDecode[$i])) {
                                    $iconSpeaker .= '<img class="ml-1 icon_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: inline-block" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker.png" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                                    $iconSpeaker .= '<img class="ml-1 icon_gif_sort-question' . ($keyQ) . '_answer' . ($keyAnswer) . '" style="width: 30px; cursor: pointer; display: none" onclick="textToSpeechBySort(event, true)" draggable="false" src="/img/speaker_animated.gif" alt="speaker" speaker-section-id="' . $section->id . '" speaker-question-id="' . $dataQuestion->id . '" speaker-key-answer="' . $i . '">';
                                }

                                $template = $this->str_replace_first("??", ' <div class="d-inline-flex">' . $iconResult . ' <div class="mt-1 style_answer pb-0 pt-18">' . (!empty($valAnswerDecode[$i]) ? $valAnswerDecode[$i] : ' ') . $iconSpeaker . '</div> ' . $strCorrectAnswer . '</div> ', $template);
                            }

                            $template = str_replace("|", " ", $template);
                            $itemC = [
                                'template' => $template
                            ];
                            array_push($itemQ['contents'], $itemC);
                        }

                        array_push($itemS['questions'], $itemQ);
                        $keyQuestion += 1;
                        break;

                    case config('trial_test.main_type.q_and_a'):
                        $itemQ = [
                            'main_type' => $dataQuestion->main_type,
                            'question_id' => $dataQuestion->id,
                            'title' => $dataQuestion->title,
                            'audio_title' => $dataQuestion->audio_title,
                            'title_play_audio_with_url' => 0,
                            'voice_for_title' => $dataQuestion->voice_for_title,
                            'voice_for_content_main' => $dataQuestion->voice_for_content_main,
                            'content_main_text' => $dataQuestion->content_main_text,
                            'content_main_picture' => $dataQuestion->content_main_picture,
                            'content_main_audio' => $dataQuestion->content_main_audio,
                            'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                            'title_play_audio' => 0,
                            'content_play_audio' => 0,
                            'sub_questions' => []
                        ];

                        if ($dataQuestion->content_main_audio) {
                            $itemQ['play_audio'] = 0;
                        }

                        $libraryQuestionQnaDetail = DB::table('library_question_qna_detail')->where('library_question_id', $dataQuestion->id)->orderBy('order', 'ASC')->get();
                        foreach ($libraryQuestionQnaDetail as $qnaDetailKey => $qnaDetailVal) {
                            $qnaCorrectAnswer = array_map('trim', array_map('strip_tags', explode("|", $qnaDetailVal->correct_answer)));
                            $qnaInCorrectAnswer = array_map('trim', array_map('strip_tags', explode("|", $qnaDetailVal->incorrect_answer)));

                            $arrShuffle = [];
                            $mergeAnswer = array_merge($qnaCorrectAnswer, $qnaInCorrectAnswer);
                            shuffle($mergeAnswer);
                            $resultStatus = false;
                            foreach ($mergeAnswer as $keyAnswer => $answer) {
                                if ($answer) {
                                    $isCorrectAnswer = 'none';
                                    $answerToChoose = 'none';

                                    // if (trim(strip_tags($valAnswerDecode[$qnaDetailKey])) == trim(strip_tags($qnaDetailVal->correct_answer)) && trim(strip_tags($qnaDetailVal->correct_answer)) == trim(strip_tags($answer))) {
                                    //   $resultStatus = true;
                                    //   $isCorrectAnswer = true;
                                    // } else {
                                    //   if (trim(strip_tags($valAnswerDecode[$qnaDetailKey])) == trim(strip_tags($answer))) {
                                    //     $isCorrectAnswer = false;
                                    //   }
                                    //   if (trim(strip_tags($qnaDetailVal->correct_answer)) == trim(strip_tags($answer))) {
                                    //     $answerToChoose = true;
                                    //   }
                                    // }

                                    if (in_array(trim($answer), $valAnswerDecode[$qnaDetailKey]) && in_array(trim($answer), $qnaCorrectAnswer)) {
                                        $resultStatus = true;
                                        $isCorrectAnswer = true;
                                    } else {
                                        if (in_array(trim($answer), $valAnswerDecode[$qnaDetailKey])) {
                                            $isCorrectAnswer = false;
                                        }
                                        if (in_array(trim($answer), $qnaCorrectAnswer)) {
                                            $answerToChoose = true;
                                        }
                                    }

                                    $itemShuffle = [
                                        'answer' => $answer,
                                        'answer_play_audio' => 0,
                                        'is_correct_answer' => $isCorrectAnswer,
                                        'answer_to_choose' => $answerToChoose
                                    ];

                                    array_push($arrShuffle, $itemShuffle);
                                }
                            }
                            $itemSubQ = [
                                'sub_question' => $qnaDetailVal->sub_question,
                                'voice_for_sub_question' => $qnaDetailVal->voice_for_sub_question,
                                'voice_for_answer' => $qnaDetailVal->voice_for_answer,
                                'subQ_play_audio' => 0,
                                'answer_play_audio' => 0,
                                'correct_answer' => explode("|", $qnaDetailVal->correct_answer),
                                'incorrect_answer' => explode("|", $qnaDetailVal->incorrect_answer),
                                'result_status' => $resultStatus,
                                'shuffle_answer' => $arrShuffle
                            ];
                            array_push($itemQ['sub_questions'], $itemSubQ);
                        };

                        array_push($itemS['questions'], $itemQ);
                        $keyQuestion += 1;
                        break;

                    case config('trial_test.main_type.matching'):
                        $itemQ = [
                            'main_type' => $dataQuestion->main_type,
                            'question_id' => $dataQuestion->id,
                            'title' => $dataQuestion->title,
                            'audio_title' => $dataQuestion->audio_title,
                            'title_play_audio_with_url' => 0,
                            'voice_for_title' => $dataQuestion->voice_for_title,
                            'answer' => $valAnswerDecode,
                            'title_play_audio' => 0,
                            'row_1' => [],
                            'row_2' => [],
                            'shuffle_row_1' => [],
                            'shuffle_row_2' => []
                        ];

                        $libraryQuestionMatchingDetail = DB::table('library_question_matching_detail')->where('library_question_id', $dataQuestion->id)->orderBy('order', 'ASC')->get();
                        foreach ($libraryQuestionMatchingDetail as $key => $value) {
                            if ($value->content_text_a || $value->picture_url_a) {
                                $itemRow1 = [
                                    'content_text_a' => $value->content_text_a,
                                    'picture_url_a' => $value->picture_url_a,
                                    'voice_for_content_text_a' => $value->voice_for_content_text_a,
                                    'play_audio' => 0
                                ];
                                array_push($itemQ['row_1'], $itemRow1);
                            }

                            if ($value->content_text_b || $value->picture_url_b || $value->audio_url) {
                                $isCorrectAnswer = false;
                                $compareValA = $value->content_text_a ? $value->content_text_a : $value->picture_url_a;
                                $compareValB = $value->audio_url;
                                if ($value->content_text_b) {
                                    $compareValB = $value->content_text_b;
                                } elseif ($value->picture_url_b) {
                                    $compareValB = $value->picture_url_b;
                                }
                                if ($compareValA && $compareValB) {
                                    foreach ($valAnswerDecode as $ans) {
                                        if (trim(strip_tags($ans[0])) == trim(strip_tags($compareValA)) && trim(strip_tags($ans[1])) == trim(strip_tags($compareValB))) {
                                            $isCorrectAnswer = true;
                                            break;
                                        }

                                        $isCorrectAnswer = false;
                                    };
                                }

                                $itemRow2 = [
                                    'content_text_b' => $value->content_text_b,
                                    'picture_url_b' => $value->picture_url_b,
                                    'audio_url' => $value->audio_url,
                                    'is_correct_answer' => $isCorrectAnswer
                                ];
                                $itemRow2['play_audio'] = 0;
                                array_push($itemQ['row_2'], $itemRow2);
                            }
                        }

                        $itemQ['shuffle_row_1'] = $itemQ['row_1'];
                        $itemQ['shuffle_row_2'] = $itemQ['row_2'];
                        shuffle($itemQ['shuffle_row_1']);
                        shuffle($itemQ['shuffle_row_2']);
                        array_push($itemS['questions'], $itemQ);
                        $keyQuestion += 1;
                        break;

                    case config('trial_test.main_type.fill_input'):
                        $arrContentMain = explode("\n", $dataQuestion->content_main_text);
                        $itemQ = [
                            'main_type' => $dataQuestion->main_type,
                            'content_main_picture' => $dataQuestion->content_main_picture,
                            'content_main_text' => $dataQuestion->content_main_text,
                            'content_main_audio' => $dataQuestion->content_main_audio,
                            'question_id' => $dataQuestion->id,
                            'title' => $dataQuestion->title,
                            'audio_title' => $dataQuestion->audio_title,
                            'title_play_audio_with_url' => 0,
                            'title_play_audio' => 0,
                            'voice_for_title' => $dataQuestion->voice_for_title,
                            'arr_content_main' => $arrContentMain,
                            'picture_bellow_text' => $dataQuestion->picture_bellow_text,
                            'contents' => []
                        ];

                        if ($dataQuestion->content_main_audio) {
                            $itemQ['play_audio'] = 0;
                        }

                        $idxTemplate = 0;
                        $arrCorrectAnswer = json_decode($dataQuestion->correct_answer);

                        foreach ($arrContentMain as $key => $value) {
                            $runWhile = true;
                            $contentMain = $arrContentMain[$key];
                            $template = $contentMain;
                            $completeAnswer = $contentMain;
                            $idxCharInContent = 0;
                            $isCorrectAnswer = true;
                            while ($runWhile) {
                                if ($idxCharInContent >= substr_count($contentMain, "??")) {
                                    $runWhile = false;
                                    break;
                                }

                                $srtAnswer = '';
                                if (isset($valAnswerDecode[$key][$idxCharInContent])) {
                                    $srtAnswer = implode(" ", $valAnswerDecode[$key][$idxCharInContent]);
                                }
                                $arrCorrectAnswerSub = [];
                                $arrCorrectAnswerSubLowercase = [];
                                if (isset($arrCorrectAnswer[$idxTemplate])) {
                                    $arrCorrectAnswerSub = explode("|", $arrCorrectAnswer[$idxTemplate]);
                                    foreach ($arrCorrectAnswerSub as $keyAnswerSub => $valAnswerSub) {
                                        array_push($arrCorrectAnswerSubLowercase, trim(strtolower(strip_tags($valAnswerSub))));
                                    }
                                }

                                $isCorrectAnswerSub = !empty(strip_tags($srtAnswer)) && !empty($arrCorrectAnswerSubLowercase) && in_array(trim(strtolower(strip_tags($srtAnswer))), $arrCorrectAnswerSubLowercase) ? true : false;

                                if ($isCorrectAnswer) {
                                    $isCorrectAnswer = $isCorrectAnswerSub;
                                }

                                $iconResult = '';
                                $styleAnswer = '';
                                if ($isCorrectAnswerSub) {
                                    $styleAnswer = 'style_typing_correct_answer';
                                    $iconResult = ' <i style="display: inline-block; margin-left: 5px; margin-right: 5px; font-size: 30px; color: #7BBB44;" class="fas fa-check-circle"></i> ';
                                } else {
                                    $styleAnswer = 'style_typing_incorrect_answer';
                                    $iconResult = ' <i style="display: inline-block; margin-left: 5px; margin-right: 5px; font-size: 30px; color: #FF0000;" class="far fa-times-circle"></i> ';
                                }
                                Log::debug(">>>>> iconResult: ");
                                Log::debug($iconResult);
                                $replaceTemplate = $this->str_replace_first("??", $iconResult . ' <span id="question-' . ($dataQuestion->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class=" ' . $styleAnswer . ' "> ' . $srtAnswer . '</span> ', $template);
                                $replaceCompleteAnswer = $this->str_replace_first("??", (!empty($arrCorrectAnswerSub) ? $arrCorrectAnswerSub[0] : ''), $completeAnswer);
                                if ($replaceTemplate) {
                                    $template = $replaceTemplate;
                                    $completeAnswer = $replaceCompleteAnswer;
                                    $idxTemplate += 1;
                                } else {
                                    $runWhile = false;
                                }

                                $idxCharInContent += 1;
                            }

                            $template = str_replace("|", " ", $template);
                            $completeAnswer = str_replace("|", " ", $completeAnswer);

                            // if (!substr_count($contentMain, "??")) {
                            //   $q3IsCorrectAnswer = 'none';
                            // }

                            $itemC = [
                                'template' => $template,
                                'complete_answer' => $completeAnswer,
                                'is_correct_answer' => $isCorrectAnswer
                            ];
                            array_push($itemQ['contents'], $itemC);
                        }


                        array_push($itemS['questions'], $itemQ);
                        $keyQuestion += 1;
                        break;
                }
            }
        }
      }

      array_push($data['sections'], $itemS);
    }

    return [
      'code' => '10000',
      'test_time' => $remainTime,
      'data' => $data,
      'message' => 'success'
    ];
  }

  public function getTrialTestPreview(Request $req)
  {
    $data = null;
    $remainTime = null;
    $idTopic = $req->id_topic;
    $data = [
      'isResult' => true,
      'type' => 'preview',
      'sections' => []
    ];
    $libraryTest = DB::table('library_test')->where('id', $idTopic)->first();
    $strSectionIds = $libraryTest->section_ids;
    $arrSectionIds = explode(",", $strSectionIds);
    $lstSection = DB::table('section')->whereIn('id', $arrSectionIds)->orderBy('id', 'ASC')->get();

    foreach ($lstSection as $section) {
      $sectionId = $section->id;
      $itemS = [
        'section_id' => $section->id,
        'section_name' => $section->section_name,
        'passage' => $section->passage,
        'questions' => []
      ];

      $lstSectionQuestion = DB::table('section_question')->join('library_question', 'library_question.id', '=', 'section_question.question_id')->where('section_id', $sectionId)->orderBy('question_order', 'ASC')->get();
      $keyQuestion = 0;
      foreach ($lstSectionQuestion as $questionVal) {
        $dataQuestion = DB::table('library_question')->where('id', $questionVal->question_id)->first();

        switch ($dataQuestion->main_type) {
          case config('trial_test.main_type.sort'):
            $correctAnswer = explode("|", $dataQuestion->correct_answer);
            $arrMerged = array_merge($correctAnswer, explode("|", $dataQuestion->incorrect_answer));
            shuffle($arrMerged);
            $itemQ = [
              'main_type' => $dataQuestion->main_type,
              'content_main_audio' => $dataQuestion->content_main_audio,
              'content_main_picture' => $dataQuestion->content_main_picture,
              'picture_bellow_text' => $dataQuestion->picture_bellow_text,
              'question_id' => $dataQuestion->id,
              'title' => $dataQuestion->title,
              'audio_title' => $dataQuestion->audio_title,
              'title_play_audio_with_url' => 0,
              'correct_answer' => $correctAnswer,
              'incorrect_answer' => explode("|", $dataQuestion->incorrect_answer),
              'shuffle_answer' => $arrMerged,
              'title_play_audio' => 0,
              'content_play_audio' => 0,
              'contents' => []
            ];
            if ($dataQuestion->content_main_audio) {
              $itemQ['play_audio'] = 0;
            }

            $contentMainText = $dataQuestion->content_main_text;
            if ($dataQuestion->category == config('trial_test.category.writing')) {
              $countChartInContentMain = substr_count($contentMainText, "??");
              $template = $contentMainText;

              for ($i = 0; $i < $countChartInContentMain; $i++) {
                $replaceTemplate = $this->str_replace_first("??", ' <div class="mt-1 style_answer">' . $correctAnswer[$i] . '</div> ', $template);
                $template = $replaceTemplate;
              }

              $template = str_replace("|", " ", $template);

              $itemC = [
                'template' => $template,
                'complete_answer' => null,
                'is_correct_answer' => null
              ];
              array_push($itemQ['contents'], $itemC);
            } else {
              $countChartInContentMain = substr_count($contentMainText, "??");
              $template = $contentMainText;

              for ($i = 0; $i < $countChartInContentMain; $i++) {
                $template = $this->str_replace_first("??", ' <div class="mt-1 style_answer">' . $correctAnswer[$i] . '</div> ', $template);
              }

              $template = str_replace("|", " ", $template);
              $itemC = [
                'template' => $template
              ];
              array_push($itemQ['contents'], $itemC);
            }

            array_push($itemS['questions'], $itemQ);
            $keyQuestion += 1;
            break;

          case config('trial_test.main_type.q_and_a'):
            $itemQ = [
              'main_type' => $dataQuestion->main_type,
              'question_id' => $dataQuestion->id,
              'title' => $dataQuestion->title,
              'audio_title' => $dataQuestion->audio_title,
              'title_play_audio_with_url' => 0,
              'content_main_text' => $dataQuestion->content_main_text,
              'content_main_picture' => $dataQuestion->content_main_picture,
              'content_main_audio' => $dataQuestion->content_main_audio,
              'picture_bellow_text' => $dataQuestion->picture_bellow_text,
              'title_play_audio' => 0,
              'content_play_audio' => 0,
              'sub_questions' => []
            ];

            if ($dataQuestion->content_main_audio) {
              $itemQ['play_audio'] = 0;
            }

            $libraryQuestionQnaDetail = DB::table('library_question_qna_detail')->where('library_question_id', $dataQuestion->id)->orderBy('order', 'ASC')->get();
            foreach ($libraryQuestionQnaDetail as $qnaDetailKey => $qnaDetailVal) {
              $qnaCorrectAnswer = explode("|", $qnaDetailVal->correct_answer);
              $qnaInCorrectAnswer = explode("|", $qnaDetailVal->incorrect_answer);

              $arrShuffle = [];
              $mergeAnswer = array_merge($qnaCorrectAnswer, $qnaInCorrectAnswer);
              shuffle($mergeAnswer);
              $resultStatus = false;
              foreach ($mergeAnswer as $keyAnswer => $answer) {
                if ($answer) {
                  $answerToChoose = 'none';

                  if (trim(strip_tags($qnaDetailVal->correct_answer)) == trim(strip_tags($answer))) {
                    $resultStatus = true;
                    $answerToChoose = true;
                  }

                  $itemShuffle = [
                    'answer' => $answer,
                    'is_correct_answer' => null,
                    'answer_to_choose' => $answerToChoose
                  ];

                  array_push($arrShuffle, $itemShuffle);
                }
              }
              $itemSubQ = [
                'sub_question' => $qnaDetailVal->sub_question,
                'correct_answer' => $qnaCorrectAnswer,
                'incorrect_answer' => $qnaInCorrectAnswer,
                'result_status' => $resultStatus,
                'shuffle_answer' => $arrShuffle,
                'countChoose' => 0
              ];
              array_push($itemQ['sub_questions'], $itemSubQ);
            };

            array_push($itemS['questions'], $itemQ);
            $keyQuestion += 1;
            break;

          case config('trial_test.main_type.matching'):
            $itemQ = [
              'main_type' => $dataQuestion->main_type,
              'question_id' => $dataQuestion->id,
              'title' => $dataQuestion->title,
              'audio_title' => $dataQuestion->audio_title,
              'title_play_audio_with_url' => 0,
              'title_play_audio' => 0,
              'answer' => null,
              'row_1' => [],
              'row_2' => [],
              'shuffle_row_1' => [],
              'shuffle_row_2' => []
            ];

            $libraryQuestionMatchingDetail = DB::table('library_question_matching_detail')->where('library_question_id', $dataQuestion->id)->orderBy('order', 'ASC')->get();
            foreach ($libraryQuestionMatchingDetail as $key => $value) {
              if ($value->content_text_a || $value->picture_url_a) {
                $itemRow1 = [
                  'content_text_a' => $value->content_text_a,
                  'picture_url_a' => $value->picture_url_a
                ];
                array_push($itemQ['row_1'], $itemRow1);
              }

              if ($value->content_text_b || $value->picture_url_b || $value->audio_url) {
                $itemRow2 = [
                  'content_text_b' => $value->content_text_b,
                  'picture_url_b' => $value->picture_url_b,
                  'audio_url' => $value->audio_url,
                  'is_correct_answer' => null
                ];
                // if ($value->audio_url) {
                $itemRow2['play_audio'] = 0;
                // }
                array_push($itemQ['row_2'], $itemRow2);
              }
            }

            $itemQ['shuffle_row_1'] = $itemQ['row_1'];
            $itemQ['shuffle_row_2'] = $itemQ['row_2'];
            shuffle($itemQ['shuffle_row_1']);
            shuffle($itemQ['shuffle_row_2']);
            array_push($itemS['questions'], $itemQ);
            $keyQuestion += 1;
            break;

          case config('trial_test.main_type.fill_input'):
            $arrContentMain = explode("\n", $dataQuestion->content_main_text);
            $itemQ = [
              'main_type' => $dataQuestion->main_type,
              'content_main_picture' => $dataQuestion->content_main_picture,
              'content_main_text' => $dataQuestion->content_main_text,
              'content_main_audio' => $dataQuestion->content_main_audio,
              'question_id' => $dataQuestion->id,
              'title' => $dataQuestion->title,
              'audio_title' => $dataQuestion->audio_title,
              'title_play_audio_with_url' => 0,
              'title_play_audio' => 0,
              'voice_for_title' => $dataQuestion->voice_for_title,
              'arr_content_main' => $arrContentMain,
              'picture_bellow_text' => $dataQuestion->picture_bellow_text,
              'contents' => []
            ];

            if ($dataQuestion->content_main_audio) {
              $itemQ['play_audio'] = 0;
            }

            $idxTemplate = 0;
            $arrCorrectAnswer = json_decode($dataQuestion->correct_answer);

            foreach ($arrContentMain as $key => $value) {
              $runWhile = true;
              $contentMain = $arrContentMain[$key];
              $template = $contentMain;
              $idxCharInContent = 0;
              while ($runWhile) {
                if ($idxCharInContent >= substr_count($contentMain, "??")) {
                  $runWhile = false;
                  break;
                }

                $arrCorrectAnswerSub = [];
                if (isset($arrCorrectAnswer[$idxTemplate])) {
                  $arrCorrectAnswerSub = explode("|", $arrCorrectAnswer[$idxTemplate]);
                }

                $replaceTemplate = $this->str_replace_first("??", ' <span id="question-' . ($dataQuestion->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class=" style_typing_correct_answer "> ' . (!empty($arrCorrectAnswerSub) ? $arrCorrectAnswerSub[0] : '') . '</span> ', $template);
                if ($replaceTemplate) {
                  $template = $replaceTemplate;
                  $idxTemplate += 1;
                } else {
                  $runWhile = false;
                }

                $idxCharInContent += 1;
              }

              $template = str_replace("|", " ", $template);

              $itemC = [
                'template' => $template,
                'complete_answer' => null,
                'is_correct_answer' => null
              ];
              array_push($itemQ['contents'], $itemC);
            }



            array_push($itemS['questions'], $itemQ);
            $keyQuestion += 1;
            break;
        }
      }

      array_push($data['sections'], $itemS);
    }

    return [
      'code' => '10000',
      'test_time' => $remainTime,
      'data' => $data,
      'message' => 'success'
    ];
  }

  public function saveTestResults(Request $req)
  {
    Log::info('saveTestResults - START >>');
    $idStudentTestResult = $req->id_student_test_result;
    $studentTestResult = StudentTestResult::where('id', $idStudentTestResult)
      ->orderBy('created_at', 'DESC')->first();
    $libraryTest = DB::table('library_test')->where('id', $studentTestResult->library_test_id)->first();
    $topicTestType = $libraryTest->test_type;

    if ($studentTestResult->end_at) {
      return [
        'code' => '10001',
        'message' => 'Test has been completed'
      ];
    }

    if ($topicTestType == config('trial_test.topic.test_type.ielts_writing')) {
      return $this->saveIeltsWritingTestResults($req);
    }

    return $this->saveTrialTestResults($req);
  }

  public function saveTrialTestResults(Request $req)
  {
    Log::info('saveTrialTestResults - START >>');
    DB::beginTransaction();
    try {
      $data = $req->data;
      $idStudentTestResult = $req->id_student_test_result;
      $testCode = $req->test_code;
      $testType = $req->test_type;

      $scoreReceived = [
        'vocabulary' => [
          'score' => 0,
          'total' => 0,
          'correct_count' => null,
          'total_question' => null
        ],
        'reading' => [
          'score' => 0,
          'total' => 0,
          'correct_count' => null,
          'total_question' => null
        ],
        'writing' => [
          'score' => 0,
          'total' => 0,
          'correct_count' => null,
          'total_question' => null
        ],
        'grammar' => [
          'score' => 0,
          'total' => 0,
          'correct_count' => null,
          'total_question' => null
        ]
      ];
      $scoresSubsQ = 0;
      $correctCountSubsQ = 0;
      $totalSubsQuestionCount = 0;
      $keyQuestion = 0;

      $studentTestResult = StudentTestResult::where('id', $idStudentTestResult)
        ->orderBy('created_at', 'DESC')->first();
      $libraryTest = DB::table('library_test')->where('id', $studentTestResult->library_test_id)->first();

      foreach ($data as $val) {
        $scoresSubQ = 0;
        $StudentTestQuestionResult = new StudentTestQuestionResult();
        $StudentTestQuestionResult->student_test_result_id = $idStudentTestResult;
        $StudentTestQuestionResult->question_id = $val['question_id'];
        $StudentTestQuestionResult->answer = json_encode($val['answer']);

        $countCorrectAnswer = 0;
        $totalSubQuestionCount = 0;
        $valAnswerDecode = $val['answer'];
        $dataQuestion = DB::table('library_question')->where('id', $val['question_id'])->first();

        switch ($dataQuestion->main_type) {
          case config('trial_test.main_type.sort'):
            $correctAnswer = explode("|", $dataQuestion->correct_answer);
            $contentMainText = $dataQuestion->content_main_text;
            if ($dataQuestion->category == config('trial_test.category.writing')) {
              $countChartInContentMain = substr_count($contentMainText, "??");
              $template = $contentMainText;
              $completeAnswer = $contentMainText;

              for ($i = 0; $i < $countChartInContentMain; $i++) {
                $replaceTemplate = $this->str_replace_first("??", ' <div class="mt-1 style_answer">' . $valAnswerDecode[$i] . '</div> ', $template);
                $replaceCompleteAnswer = $this->str_replace_first("??", $correctAnswer[$i], $completeAnswer);
                $template = $replaceTemplate;
                $completeAnswer = $replaceCompleteAnswer;

                $isCorrectAnswer = trim(strip_tags($correctAnswer[$i])) == trim(strip_tags($valAnswerDecode[$i])) ? 1 : 0;
              }

              if (!$isCorrectAnswer) {
              } else {
                $countCorrectAnswer += 1;
              }

              if (substr_count($contentMainText, "??")) {
                $totalSubQuestionCount += 1;
              }
            } else {
              $countChartInContentMain = substr_count($contentMainText, "??");

              for ($i = 0; $i < $countChartInContentMain; $i++) {
                $isCorrectAnswer = trim(strip_tags($correctAnswer[$i])) == trim(strip_tags($valAnswerDecode[$i])) ? 1 : 0;
                if (!$isCorrectAnswer) {
                } else {
                  $countCorrectAnswer += 1;
                }

                $totalSubQuestionCount += 1;
              }
            }

            $keyQuestion += 1;
            break;

          case config('trial_test.main_type.q_and_a'):
            $libraryQuestionQnaDetail = DB::table('library_question_qna_detail')->where('library_question_id', $dataQuestion->id)->orderBy('order', 'ASC')->get();
            if ($libraryTest->test_type == config('trial_test.topic.test_type.common')) {
              foreach ($libraryQuestionQnaDetail as $qnaDetailKey => $qnaDetailVal) {
                $qnaCorrectAnswer = array_map('trim', array_map('strip_tags', explode("|", $qnaDetailVal->correct_answer)));
                $resultStatus = 0;
                $totalCorrectAnswer = 0;

                foreach ($valAnswerDecode[$qnaDetailKey] as $keyAnswer => $answer) {
                  if (in_array(trim($answer), $qnaCorrectAnswer)) {
                    $totalCorrectAnswer += 1;
                  }
                }

                if ($totalCorrectAnswer == count($qnaCorrectAnswer)) {
                  $resultStatus = 1;
                }

                if (!$resultStatus) {
                } else {
                  $countCorrectAnswer += 1;
                }

                $totalSubQuestionCount += 1;
              };
            } else {
              foreach ($libraryQuestionQnaDetail as $qnaDetailKey => $qnaDetailVal) {
                $qnaCorrectAnswer = array_map('trim', array_map('strip_tags', explode("|", $qnaDetailVal->correct_answer)));
                $totalCorrectAnswer = 0;

                foreach ($valAnswerDecode[$qnaDetailKey] as $keyAnswer => $answer) {
                  if (in_array(trim($answer), $qnaCorrectAnswer)) {
                    $countCorrectAnswer += 1;
                  }
                }

                $totalSubQuestionCount += count($qnaCorrectAnswer);
              };
            }

            $keyQuestion += 1;
            break;

          case config('trial_test.main_type.matching'):
            $libraryQuestionMatchingDetail = DB::table('library_question_matching_detail')->where('library_question_id', $dataQuestion->id)->orderBy('order', 'ASC')->get();
            foreach ($libraryQuestionMatchingDetail as $key => $value) {
              if ($value->content_text_b || $value->picture_url_b || $value->audio_url) {
                $isCorrectAnswer = 'none';
                $compareValA = $value->content_text_a ? $value->content_text_a : $value->picture_url_a;
                $compareValB = $value->audio_url;
                if ($value->content_text_b) {
                  $compareValB = $value->content_text_b;
                } elseif ($value->picture_url_b) {
                  $compareValB = $value->picture_url_b;
                }
                if ($compareValA && $compareValB) {
                  $totalSubQuestionCount += 1;

                  foreach ($valAnswerDecode as $ans) {
                    if (trim(strip_tags($ans[0])) == trim(strip_tags($compareValA)) && trim(strip_tags($ans[1])) == trim(strip_tags($compareValB))) {
                      $isCorrectAnswer = 1;
                      break;
                    }

                    $isCorrectAnswer = 0;
                  };
                }

                if (!$isCorrectAnswer || $isCorrectAnswer == 'none') {
                } else {
                  $countCorrectAnswer += 1;
                }
              }
            }

            $keyQuestion += 1;
            break;

          case config('trial_test.main_type.fill_input'):
            $arrContentMain = explode("\n", $dataQuestion->content_main_text);
            $idxTemplate = 0;
            $arrCorrectAnswer = json_decode($dataQuestion->correct_answer);

            foreach ($arrContentMain as $key => $value) {
              $runWhile = true;
              $contentMain = $arrContentMain[$key];
              $template = $contentMain;
              $idxCharInContent = 0;
              while ($runWhile) {
                if ($idxCharInContent >= substr_count($contentMain, "??")) {
                  $runWhile = false;
                  break;
                }

                $srtAnswer = '';
                if (isset($valAnswerDecode[$key][$idxCharInContent])) {
                  $srtAnswer = implode(" ", $valAnswerDecode[$key][$idxCharInContent]);
                }
                $arrCorrectAnswerSub = [];
                $arrCorrectAnswerSubLowercase = [];
                if (isset($arrCorrectAnswer[$idxTemplate])) {
                  $arrCorrectAnswerSub = explode("|", $arrCorrectAnswer[$idxTemplate]);
                  foreach ($arrCorrectAnswerSub as $keyAnswerSub => $valAnswerSub) {
                    array_push($arrCorrectAnswerSubLowercase, trim(strtolower(strip_tags($valAnswerSub))));
                  }
                }
                $replaceTemplate = $this->str_replace_first("??", ' <span id="question-' . ($dataQuestion->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class="custom_fill_input" contenteditable>' . $srtAnswer . '</span> ', $template);
                if ($replaceTemplate) {
                  $isCorrectAnswerSub = !empty(strip_tags($srtAnswer)) && !empty($arrCorrectAnswerSubLowercase) && in_array(trim(strtolower(strip_tags($srtAnswer))), $arrCorrectAnswerSubLowercase) ? 1 : 0;

                  if (!$isCorrectAnswerSub) {
                  } else {
                    $countCorrectAnswer += 1;
                  }

                  $template = $replaceTemplate;
                  $idxTemplate += 1;
                  $totalSubQuestionCount += 1;
                } else {
                  $runWhile = false;
                }

                $idxCharInContent += 1;
              }
            }

            $keyQuestion += 1;

            break;
          case config('trial_test.main_type.drop_down'):
            $arrContentMain = explode("\n", $dataQuestion->content_main_text);
            $idxTemplate = 0;
            $arrCorrectAnswer = explode("|", $dataQuestion->correct_answer);

            foreach ($arrContentMain as $key => $value) {
              $runWhile = true;
              $contentMain = $arrContentMain[$key];
              $template = $contentMain;
              $idxCharInContent = 0;
              while ($runWhile) {
                if ($idxCharInContent >= substr_count($contentMain, "??")) {
                  $runWhile = false;
                  break;
                }

                $srtAnswer = '';
                if (isset($valAnswerDecode[$key][$idxCharInContent])) {
                  $srtAnswer = implode(" ", $valAnswerDecode[$key][$idxCharInContent]);
                }
                $correctAnswerSub = null;
                if (isset($arrCorrectAnswer[$idxTemplate])) {
                  $correctAnswerSub = $arrCorrectAnswer[$idxTemplate];
                }
                $replaceTemplate = $this->str_replace_first("??", ' <input id="question-' . ($dataQuestion->id) . '_content-' . ($key + 1) . '_input-' . ($idxCharInContent + 1) . '" class="form-control" style="min-width: 300px; display: inline-block; width: unset;" type="text" value="' . $srtAnswer . '"> ', $template);
                if ($replaceTemplate) {
                  $isCorrectAnswerSub = !empty(strip_tags($srtAnswer)) && !empty($correctAnswerSub) && trim($srtAnswer) == trim($correctAnswerSub) ? 1 : 0;

                  if (!$isCorrectAnswerSub) {
                  } else {
                    $countCorrectAnswer += 1;
                  }

                  $template = $replaceTemplate;
                  $idxTemplate += 1;
                  $totalSubQuestionCount += 1;
                } else {
                  $runWhile = false;
                }

                $idxCharInContent += 1;
              }
            }

            $keyQuestion += 1;
            break;
        }

        if ($countCorrectAnswer) {
          $libraryQuestion = DB::table('library_question')->where('id', $val['question_id'])->first();
          switch ($libraryQuestion->category) {
            case config('trial_test.category.vocabulary'):
              if ($scoreReceived['vocabulary']['correct_count'] === null) {
                $scoreReceived['vocabulary']['correct_count'] = 0;
              }

              $scoreReceived['vocabulary']['score'] += ($libraryQuestion->scores * $countCorrectAnswer);
              $scoreReceived['vocabulary']['correct_count'] += $countCorrectAnswer;
              break;

            case config('trial_test.category.reading'):
              if ($scoreReceived['reading']['correct_count'] === null) {
                $scoreReceived['reading']['correct_count'] = 0;
              }

              $scoreReceived['reading']['score'] += ($libraryQuestion->scores * $countCorrectAnswer);
              $scoreReceived['reading']['correct_count'] += $countCorrectAnswer;
              break;

            case config('trial_test.category.writing'):
              if ($scoreReceived['writing']['correct_count'] === null) {
                $scoreReceived['writing']['correct_count'] = 0;
              }

              $scoreReceived['writing']['score'] += ($libraryQuestion->scores * $countCorrectAnswer);
              $scoreReceived['writing']['correct_count'] += $countCorrectAnswer;
              break;

            case config('trial_test.category.grammar'):
              if ($scoreReceived['grammar']['correct_count'] === null) {
                $scoreReceived['grammar']['correct_count'] = 0;
              }

              $scoreReceived['grammar']['score'] += ($libraryQuestion->scores * $countCorrectAnswer);
              $scoreReceived['grammar']['correct_count'] += $countCorrectAnswer;
              break;
          }

          $scoresSubQ = $libraryQuestion->scores * $countCorrectAnswer;
          $correctCountSubsQ += $countCorrectAnswer;
          $scoresSubsQ += $scoresSubQ;
          $totalSubsQuestionCount += $totalSubQuestionCount;
        }

        $libraryQuestion = DB::table('library_question')->where('id', $val['question_id'])->first();
        switch ($libraryQuestion->category) {
          case config('trial_test.category.vocabulary'):
            if ($scoreReceived['vocabulary']['total_question'] === null) {
              $scoreReceived['vocabulary']['total_question'] = 0;
            }

            $scoreReceived['vocabulary']['total'] += ($libraryQuestion->scores * $totalSubQuestionCount);
            $scoreReceived['vocabulary']['total_question'] += $totalSubQuestionCount;
            break;

          case config('trial_test.category.reading'):
            if ($scoreReceived['reading']['total_question'] === null) {
              $scoreReceived['reading']['total_question'] = 0;
            }

            $scoreReceived['reading']['total'] += ($libraryQuestion->scores * $totalSubQuestionCount);
            $scoreReceived['reading']['total_question'] += $totalSubQuestionCount;
            break;

          case config('trial_test.category.writing'):
            if ($scoreReceived['writing']['total_question'] === null) {
              $scoreReceived['writing']['total_question'] = 0;
            }

            $scoreReceived['writing']['total'] += ($libraryQuestion->scores * $totalSubQuestionCount);
            $scoreReceived['writing']['total_question'] += $totalSubQuestionCount;
            break;

          case config('trial_test.category.grammar'):
            if ($scoreReceived['grammar']['total_question'] === null) {
              $scoreReceived['grammar']['total_question'] = 0;
            }

            $scoreReceived['grammar']['total'] += ($libraryQuestion->scores * $totalSubQuestionCount);
            $scoreReceived['grammar']['total_question'] += $totalSubQuestionCount;
            break;
        }

        $StudentTestQuestionResult->total_sub_question_count = $totalSubQuestionCount;
        $StudentTestQuestionResult->correct_count = $countCorrectAnswer;
        $StudentTestQuestionResult->scores = $scoresSubQ;
        $StudentTestQuestionResult->save();
      }

      $scoreScaleReceived = [
        'vocabulary' => $scoreReceived['vocabulary']['total_question'] !== null ? $this->floorp((10 / $scoreReceived['vocabulary']['total_question']) * $scoreReceived['vocabulary']['correct_count'], 1) : null,
        'reading' => $scoreReceived['reading']['total_question'] !== null ? $this->floorp((10 / $scoreReceived['reading']['total_question']) * $scoreReceived['reading']['correct_count'], 1) : null,
        'writing' => $scoreReceived['writing']['total_question'] !== null ? $this->floorp((10 / $scoreReceived['writing']['total_question']) * $scoreReceived['writing']['correct_count'], 1) : null,
        'grammar' => $scoreReceived['grammar']['total_question'] !== null ? $this->floorp((10 / $scoreReceived['grammar']['total_question']) * $scoreReceived['grammar']['correct_count'], 1) : null
      ];

      $totalQuestions = array_sum([
        $scoreReceived['vocabulary']['total_question'],
        $scoreReceived['reading']['total_question'],
        $scoreReceived['writing']['total_question'],
        $scoreReceived['grammar']['total_question']
      ]);

      $resultTestIelts = [
        'total_questions' => $totalQuestions,
        'total_correct_answers' => $correctCountSubsQ
      ];

      $dateTimeNow = Carbon::now()->format('Y-m-d H:i:s');
      StudentTestResult::where('id', $idStudentTestResult)
        ->update([
          'end_at' => $dateTimeNow,
          'total_subs_question_count' => $totalSubsQuestionCount,
          'correct_count' => $correctCountSubsQ,
          'scores' => $scoresSubsQ,
          'vocabulary_score' => $scoreScaleReceived['vocabulary'],
          'reading_score' => $scoreScaleReceived['reading'],
          'writing_score' => $scoreScaleReceived['writing'],
          'grammar_score' => $scoreScaleReceived['grammar']
        ]);

      if ($idStudentTestResult) {
        // $studentTestResult = StudentTestResult::where('id', $idStudentTestResult)
        //   ->orderBy('created_at', 'DESC')->first();
        // $libraryTest = DB::table('library_test')->where('id', $studentTestResult->library_test_id)->first();

        $scoreScaleReceivedForMBooking = $scoreScaleReceived;
        $testStartTime = Carbon::parse($studentTestResult->test_start_time)->timestamp * 1000;
        if (
          $libraryTest->test_type == config('trial_test.topic_test_type.ielts_grammar') ||
          $studentTestResult->result_type == config('trial_test.result_type.homework_ielts') ||
          $libraryTest->test_type == config('trial_test.topic_test_type.ielts_reading') ||
          $libraryTest->test_type == config('trial_test.topic_test_type.ielts_listening')
        ) {
          if (
            $libraryTest->test_type == config('trial_test.topic_test_type.ielts_reading') ||
            $libraryTest->test_type == config('trial_test.topic_test_type.ielts_listening')
          ) {
            $resultTestIelts['score'] = $this->bandScore($correctCountSubsQ);
          } else {
            $percentCorrectAnswers = 0;
            if ($correctCountSubsQ > 0 && $totalQuestions > 0) {
              $percentCorrectAnswers = round(($correctCountSubsQ / $totalQuestions) * 100);
            }

            $resultTestIelts['percent_correct_answers'] = $percentCorrectAnswers;
          }

          $testType = $libraryTest->test_type;
          $scoreScaleReceivedForMBooking = $resultTestIelts;
          $scoreScaleReceivedForMBooking['test_start_time'] = $testStartTime;
        }

        $scoreScaleReceivedForMBooking['submission_time'] = Carbon::parse($dateTimeNow)->timestamp * 1000;

        if ($studentTestResult->url_callback) {
          $client = new Client();
          try {
            $urlSaveBooking = env('BACKEND_API_URL') . $studentTestResult->url_callback;
            Log::debug($urlSaveBooking);
            Log::debug(json_encode((object) $scoreScaleReceivedForMBooking));
            $responseApi = $client->put($urlSaveBooking, [
              'form_params' => [
                'test_result_id' => $idStudentTestResult,
                'test_result' => json_encode((object) $scoreScaleReceivedForMBooking),
                'test_start_time' => $testStartTime
              ],
              'headers' => [
                'api-key' => env('BACKEND_API_APP_KEY')
              ]
            ]);

            $responseApiArray = json_decode($responseApi->getBody()->getContents(), true);
            Log::debug($responseApiArray);
            if ($responseApiArray['code'] != 10000) {
              DB::rollback();
              Log::error('saveTrialTestResults - booking - ERROR');
              $msg = 'Lỗi hệ thống, hãy liên hệ với admin';

              if ($responseApiArray['message']) {
                $msg = $responseApiArray['message'];
              }

              return [
                'code' => '10001',
                'message' => $msg
              ];
            }
          } catch (\GuzzleHttp\Exception\ClientException $e) {
            DB::rollback();
            Log::error('saveTrialTestResults - booking - ERROR');
            Log::error($e);
            $msg = 'Lỗi hệ thống, hãy liên hệ với admin';

            $responseApiArray = json_decode($e->getResponse()->getBody()->getContents(), true);
            if ($responseApiArray['message']) {
              $msg = $responseApiArray['message'];
            }
            return [
              'code' => '10001',
              'message' => $msg
            ];
          }
        }
      }
      DB::commit();
      Log::info('saveTrialTestResults - END <<');
      return [
        'code' => '10000',
        'test_result_id' => $idStudentTestResult,
        'test_code' => $testCode,
        'score_scale_received' => $scoreScaleReceived,
        'result_test_ielts' => $resultTestIelts,
        'test_type' => $testType,
        'result_type' => $studentTestResult->result_type,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      Log::error('saveTrialTestResults - ERROR');
      Log::error($e);
      return [
        'code' => '10001',
        'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
      ];
    }
  }

  public function saveIeltsWritingTestResults(Request $req)
  {
    Log::info('saveIeltsWritingTestResults - START >>');
    DB::beginTransaction();
    try {
      $data = $req->data;
      $idStudentTestResult = $req->id_student_test_result;
      $testCode = $req->test_code;

      foreach ($data as $val) {
        $StudentTestQuestionResult = new StudentTestQuestionResult();
        $StudentTestQuestionResult->student_test_result_id = $idStudentTestResult;
        $StudentTestQuestionResult->question_id = $val['question_id'];
        $StudentTestQuestionResult->answer = $val['answer'];
        $StudentTestQuestionResult->total_sub_question_count = 1;
        $StudentTestQuestionResult->save();
      }

      $totalSubsQuestionCount = count($data);
      $dateTimeNow = Carbon::now()->format('Y-m-d H:i:s');
      StudentTestResult::where('id', $idStudentTestResult)
        ->update([
          'end_at' => $dateTimeNow,
          'total_subs_question_count' => $totalSubsQuestionCount
        ]);

      if ($idStudentTestResult) {
        $studentTestResult = StudentTestResult::where('id', $idStudentTestResult)
          ->orderBy('created_at', 'DESC')->first();

        $testStartTime = Carbon::parse($studentTestResult->test_start_time)->timestamp * 1000;
        $scoreScaleReceivedForMBooking = [
          'total_subs_question_count' => $totalSubsQuestionCount,
          'test_start_time' => $testStartTime,
          'submission_time' => Carbon::parse($dateTimeNow)->timestamp * 1000
        ];

        if ($studentTestResult->url_callback) {
          $client = new Client();
          try {
            $urlSaveBooking = env('BACKEND_API_URL') . $studentTestResult->url_callback;
            Log::debug($urlSaveBooking);
            Log::debug(json_encode((object) $scoreScaleReceivedForMBooking));
            $responseApi = $client->put($urlSaveBooking, [
              'form_params' => [
                'test_result_id' => $idStudentTestResult,
                'test_result' => json_encode((object) $scoreScaleReceivedForMBooking)
              ],
              'headers' => [
                'api-key' => env('BACKEND_API_APP_KEY')
              ]
            ]);

            $responseApiArray = json_decode($responseApi->getBody()->getContents(), true);
            Log::debug($responseApiArray);
            if ($responseApiArray['code'] != 10000) {
              DB::rollback();
              Log::error('saveIeltsWritingTestResults - booking - ERROR');
              $msg = 'Lỗi hệ thống, hãy liên hệ với admin';

              if ($responseApiArray['message']) {
                $msg = $responseApiArray['message'];
              }

              return [
                'code' => '10001',
                'message' => $msg
              ];
            }
          } catch (\GuzzleHttp\Exception\ClientException $e) {
            DB::rollback();
            Log::error('saveIeltsWritingTestResults - booking - ERROR');
            Log::error($e);
            $msg = 'Lỗi hệ thống, hãy liên hệ với admin';

            $responseApiArray = json_decode($e->getResponse()->getBody()->getContents(), true);
            if ($responseApiArray['message']) {
              $msg = $responseApiArray['message'];
            }
            return [
              'code' => '10001',
              'message' => $msg
            ];
          }
        }
      }
      DB::commit();
      Log::info('saveIeltsWritingTestResults - END <<');
      return [
        'code' => '10000',
        'test_result_id' => $idStudentTestResult,
        'test_code' => $testCode,
        'score_scale_received' => null,
        'result_test_ielts' => null,
        'test_type' => config('trial_test.topic.test_type.ielts_writing'),
        'result_type' => $studentTestResult->result_type,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      Log::error('saveIeltsWritingTestResults - ERROR');
      Log::error($e);
      return [
        'code' => '10001',
        'message' => 'Lỗi hệ thống, hãy liên hệ với admin'
      ];
    }
  }

  private function floorp($val, $precision)
  {
    $mult = pow(10, $precision); // Can be cached in lookup table
    return floor($val * $mult) / $mult;
  }

  private function bandScore($totalQuestions)
  {
    if ($totalQuestions >= 39) {
      return 9.0;
    } else if ($totalQuestions >= 37) {
      return 8.5;
    } else if ($totalQuestions >= 35) {
      return 8.0;
    } else if ($totalQuestions >= 33) {
      return 7.5;
    } else if ($totalQuestions >= 30) {
      return 7.0;
    } else if ($totalQuestions >= 27) {
      return 6.5;
    } else if ($totalQuestions >= 23) {
      return 6.0;
    } else if ($totalQuestions >= 20) {
      return 5.5;
    } else if ($totalQuestions >= 16) {
      return 5.0;
    } else if ($totalQuestions >= 13) {
      return 4.5;
    } else if ($totalQuestions >= 10) {
      return 4.0;
    } else if ($totalQuestions >= 7) {
      return 3.5;
    } else if ($totalQuestions >= 5) {
      return 3.0;
    } else if ($totalQuestions >= 3) {
      return 2.5;
    } else if ($totalQuestions == 2) {
      return 2.0;
    } else if ($totalQuestions == 1) {
      return 1.0;
    } else {
      return 0;
    }
  }
}
