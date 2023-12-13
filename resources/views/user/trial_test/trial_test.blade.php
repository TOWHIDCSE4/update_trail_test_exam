@extends('layout.user.main')
@push('css')
<style>
  body {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    -o-user-select: none;
    user-select: none;
  }

  .custom_fill_input {
    max-width: 100%;
    display: inline-block;
    border-bottom: 2px dotted;
    background-color: #d5dce1;
    min-width: 100px;
    cursor: auto !important;
  }

  .error_msg {
    color: red !important;
    font-style: italic !important;
  }

  .box-score_ielts_grammar {
    font-weight: 500;
    font-size: 24px;
  }

  .pt-18 {
    padding-top: 18px !important;
  }

  .style_typing_correct_answer {
    background-color: #7BBB44;
    color: white;
    padding: 0 10px;
  }

  .style_typing_incorrect_answer {
    background-color: #FF0000;
    color: white;
    padding: 0 10px;
  }

  .color_white {
    color: white !important;
  }

  #trial_test div,
  #trial_test span {
    cursor: context-menu;
  }

  .qa_answer {
    min-height: 50px;
    width: 100%;
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }

  .audio-qa {
    width: 60%;
  }

  .number_question {
    color: #7BBB44;
    font-size: 40px;
  }

  .choose_answer {
    background-color: #7BBB44;
    color: white !important;
  }

  .disable_no_choose {
      background-color: #d1d3ce;
      color: white !important;
  }

  .correct_answer {
    padding: 10px;
    background-color: #F5F5F5;
    margin-top: 10px;
    margin-bottom: 10px;
  }

  /* Collapse - START */
  .btn-step-progress {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    background-color: white;
    border: 3px solid #7D9195
  }

  .btn-step-progress:hover {
    background-color: #71a6da;
    border: 3px solid #71a6da;
  }

  .style_active {
    background-color: #FEC300;
    color: white;
    border-color: #FEC300;
  }

  /* Collapse - END */

  /* Audio player - START */
  .player {
    height: 95vh;
    display: flex;
    align-items: center;
    flex-direction: column;
    /* justify-content: center; */
  }

  .details {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    margin-top: 25px;
  }

  .track-art {
    margin: 25px;
    height: 250px;
    width: 250px;
    background-image: url("https://images.pexels.com/photos/262034/pexels-photo-262034.jpeg?auto=compress&cs=tinysrgb&dpr=3&h=750&w=1260");
    background-size: cover;
    border-radius: 15%;
  }

  .now-playing {
    font-size: 1rem;
  }

  .track-name {
    font-size: 3rem;
  }

  .track-artist {
    font-size: 1.5rem;
  }

  .buttons {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
  }

  .playpause-track,
  .prev-track,
  .next-track {
    padding: 25px;
    opacity: 0.8;

    /* Smoothly transition the opacity */
    transition: opacity .2s;
  }

  .playpause-track:hover,
  .prev-track:hover,
  .next-track:hover {
    opacity: 1.0;
  }

  .slider_container {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* Modify the appearance of the slider */
  .seek_slider,
  .volume_slider {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    height: 5px;
    background: black;
    opacity: 0.7;
    -webkit-transition: .2s;
    transition: opacity .2s;
  }

  /* Modify the appearance of the slider thumb */
  .seek_slider::-webkit-slider-thumb,
  .volume_slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 15px;
    height: 15px;
    background: white;
    cursor: pointer;
    border-radius: 50%;
  }

  .seek_slider:hover,
  .volume_slider:hover {
    opacity: 1.0;
  }

  .seek_slider {
    width: 60%;
  }

  .volume_slider {
    width: 30%;
  }

  .current-time,
  .total-duration {
    padding: 10px;
  }

  i.fa-volume-down,
  i.fa-volume-up {
    padding: 10px;
  }

  i.fa-play-circle,
  i.fa-pause-circle,
  i.fa-step-forward,
  i.fa-step-backward {
    cursor: pointer;
  }

  /* Audio player - END */

  /* Drag - START */

  .question-3-style_answer {
    display: inline-block;
    min-height: 30px;
    min-width: 150px;
    border: 3px dotted black;
    margin-right: 10px;
    margin-left: 10px;
    padding: 5px
  }

  .style_template {
    display: inline-block;
    min-height: 30px;
    min-width: 150px;
    border: 2px solid #7BBB44;
    margin-left: 10px;
    margin-right: 10px;
    padding: 6px;
    word-wrap: break-word;
    text-align: center;
  }

  .style_answer {
    display: inline-block;
    min-height: 30px;
    min-width: 150px;
    border: 2px ridge #f8f9fa;
    margin-left: 10px;
    margin-right: 10px;
    padding: 6px;
    word-wrap: break-word;
    text-align: center;
  }

  .style_correct_answer {
    display: inline-block;
    min-height: 50px;
    min-width: 150px;
    border: 3px solid #F5F5F5;
    background-color: #F5F5F5;
    margin-left: 10px;
    margin-right: 10px;
    padding: 10px;
    word-wrap: break-word;
    text-align: center;
  }

  .hidden {
    display: none;
  }

  .set_width_150 {
    min-width: 150px;
  }

  .set_style_question_3 {
    min-height: 30px;
    margin-right: 10px;
    margin-left: 10px;
    padding: 5px
  }

  .icon-style {
    font-size: 70px;
    color: #51595C;
    margin-bottom: 20px;
  }

  .icon-style:hover {
    opacity: 0.7;
  }

  .qa-answer-hover {
    border: 2px solid #7D9195;
    padding: 6px;
  }

  .cursor-hover {
    cursor: pointer !important;
  }

  .cursor-hover:hover {
    border: 2px solid #71a6da !important;
  }

  .item-match-active {
    border: 4px solid #0b71d7 !important;
  }

  .card-question {
    border-radius: 10px;
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
  }

  /* Drag - END */
</style>
@endpush
@section('content')
<!-- MAIN CONTENT -->
<div id="trial_test" style="border-radius: 3px;background-color: #F5F5F5; padding: 20px 0 0 0; font-size: 18px; display: none">
  <div v-if="!items?.isResult" class="row box_remain_time" style="position: fixed; top: 0; z-index: 99999; width: 100%">
    <div class="card col-sm-12" style="font-weight: bold; text-align: center;margin-left: 15px; box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);">
      <h1 class="card-title">Remain time <span id="time"></span></h1>
    </div>
  </div>
  <div v-if="items.topic_test_type == 'IELTS_READING'" id="ielts_reading_container" style="margin-top: 45px;">
    <div v-if="items.sections" v-for="(section, keySection) in items.sections">
      <div class="row steps-fields" style="margin-left: 0; margin-right: 0;">
        <div class="card col-sm-12 card-question accordion" style="width: 18rem;" id="section_steps">
          <div :id="'s' + (keySection) + '_edit-step'" :class="'card-body collapse s_collapse ' + (keySection == 0 ? 'show' : '')" style="font-weight: bold;" aria-expanded="false" aria-labelledby="headingOne" data-parent="#section_steps">
            <h5 class="card-title section_name" style="margin-left: -15px !important;">
              @{{ section.section_name }}
            </h5>
            <div class="row">
              <div class="col-xs-12 col-md-6 box_passage" :style="'width: 100%; overflow-y: scroll; border: 1px solid #51595C; border-radius: 5px; padding: 10px; background-color: #D7E6DC; height: ' + passageAndQuestionBoxHeight + 'px'">
                <div class="pl-3 pr-3" style="text-align: left; white-space: pre-wrap; font-weight: normal;" :id="'section-' + (keySection + 1) + '-content'" v-html="section?.passage"></div>
              </div>
              <div class="col-xs-12 col-md-6 box_question" :style="'width: 100%; overflow-y: scroll; border: 1px solid #51595C; border-radius: 5px; padding: 10px; height: ' + passageAndQuestionBoxHeight + 'px'">
                <div v-if="section?.questions" v-for="(question, keyQuestion) in section.questions" style="margin-bottom: 20px;">
                  <div class="row" style="margin-left: 0; margin-right: 0;">
                    <div v-if="question.main_type == 1" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div drag-parent-id="container-answer-sort" style="margin-bottom: 50px; font-size: 16px; font-weight: normal">
                          <div v-if="(question.content_main_picture && question.picture_bellow_text == 0) || question.content_main_audio" style="margin-bottom: 50px; text-align: center;">
                            <img v-if="question.content_main_picture && question.picture_bellow_text == 0" style="max-width: 600px; max-height: 300px;" v-if="question.content_main_picture" :src="question.content_main_picture" alt="question_img">
                            <br />
                            <br />
                            <div v-if="question.content_main_audio">
                              <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                                <source :src="question.content_main_audio" type="audio/mpeg">
                              </audio>
                              <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                              <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            </div>
                          </div>
                          <div v-if="question.category == 3" v-for="(content, keyContent) in question.contents">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <div style="display: flex; align-items: center; margin-bottom: 20px;" :id="'question-' + (question.question_id) + '-content'">
                              <div v-if="items?.isResult && content?.is_correct_answer != 'none' && items?.type != 'preview'" style="padding-right: 20px; min-width: 70px">
                                <i v-if="content?.is_correct_answer == true" style="font-size: 50px; color: #7BBB44;" class="fas fa-check-circle"></i>
                                <i v-else style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                              </div>
                              <div style="white-space: pre-wrap;" v-html="content?.template"></div>
                            </div>
                            <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                              <p>Correct</p>
                              <p style="padding-left: 10px; margin-bottom: 0; white-space: pre-wrap;" v-html="content?.complete_answer"></p>
                            </div>
                          </div>

                          <div v-else v-for="(content, keyContent) in question.contents">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <div style="display: flex; align-items: center; margin-bottom: 20px;" :id="'question-' + (question.question_id) + '-content'">
                              <div style="white-space: pre-wrap" v-html="content?.template"></div>
                            </div>
                          </div>

                          <div v-if="question.content_main_picture && question.picture_bellow_text == 1" style="margin-top: 20px; margin-bottom: 20px; text-align: center;">
                            <img style="max-width: 600px; max-height: 300px;" v-if="question.content_main_picture" :src="question.content_main_picture" alt="question_img">
                          </div>
                        </div>

                        <div v-if="!items?.isResult && items?.type != 'preview'" style="display: flex; flex-flow: row wrap; font-weight: normal" drag-parent-id="container-template-sort">
                          <div v-for="(answer, keyAnswer) in question.shuffle_answer" v-if="answer.answer" :id="'question-' + (question.question_id) + '-drag' + (keyAnswer + 1)" class="container_drag mt-1 style_template" draggable="true" style="cursor: move !important;">
                            <span style="cursor: move !important;" v-html="answer.answer"></span>
                            <img v-if="question.voice_for_answer == 1" :class="'icon_sort-question' + (question.question_id) + '_answer' + (keyAnswer+1)" style="width: 30px; cursor: pointer; display: inline-block" onclick="textToSpeechBySort(event, false)" draggable="false" src="/img/speaker.png" alt="speaker" :speaker-section-id="section.section_id" :speaker-question-id="question.question_id" :speaker-key-answer="keyAnswer">
                            <img v-if="question.voice_for_answer == 1" :class="'icon_gif_sort-question' + (question.question_id) + '_answer' + (keyAnswer+1)" style="width: 30px; cursor: pointer; display: none" onclick="textToSpeechBySort(event, false)" draggable="false" src="/img/speaker_animated.gif" alt="speaker" :speaker-section-id="section.section_id" :speaker-question-id="question.question_id" :speaker-key-answer="keyAnswer">
                          </div>
                        </div>
                      </div>
                    </div>

                    <div v-else-if="question.main_type == 2 && question.sub_questions.length == 1" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" :id="'question-' + (question.question_id)" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div style="margin-bottom: 50px; text-align: center; width: 100%; font-weight: normal">
                          <img v-if="question?.content_main_picture && question?.picture_bellow_text == 0" style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                          <div v-if="question?.content_main_text" class="mt-1 mb-1 text-left">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                          </div>
                          <div style="text-align: left; white-space: pre-wrap;" v-if="question?.content_main_text" :id="'question-' + (question.question_id) + '-content'" v-html="question?.content_main_text"></div>
                          <div style="text-align: left; white-space: pre-wrap;" v-if="question?.sub_questions[0].sub_question">
                            <span v-html="question?.sub_questions[0].sub_question"></span>
                            <img v-if="question?.sub_questions[0].voice_for_sub_question == 1 && question?.sub_questions[0].subQ_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, 0)">
                            <img v-if="question?.sub_questions[0].voice_for_sub_question == 1 && question?.sub_questions[0].subQ_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, 0)">
                          </div>
                          <img v-if="question?.content_main_picture && question?.picture_bellow_text == 1" style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                          <div v-if="question?.content_main_audio">
                            <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.content_main_audio" type="audio/mpeg">
                            </audio>
                            <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                          </div>
                        </div>
                        <div :id="'q' + (question.question_id) + '_edit-step1'" style="font-weight: normal">
                          <div class="align-content-center qa-answer-hover cursor-hover" v-for="(answer, keyAnswer) in question.sub_questions[0].shuffle_answer" v-on:click.prevent="clickAnswer(items?.isResult, question.question_id, 0, keyAnswer, question.sub_questions[0].correct_answer.length, section.section_id)" style="display: inline-flex; width: 100%;" :class="'qna_answer_'+ section.section_id + '_'+question.question_id + '_sub_q0' + ' ' + 'qa_answer q' + (question.question_id) + '_edit-step1_answer ' + 'q' + (question.question_id) + '_edit-step1_answer' + (keyAnswer+1) + ' ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'choose_answer' : '')">
                            <div v-if="!items?.isResult || answer?.answer_to_choose == true || items?.type == 'preview' || (answer?.is_correct_answer == 'none' && answer?.answer_to_choose == 'none')" :class="(items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')" style="cursor: pointer;color: #7BBB44; font-size: 30px; margin-right: 10px; min-width: 70px; text-align: center;" v-html="keyAnswer+1"></div>
                            <div v-if="items?.isResult && answer?.is_correct_answer != 'none' && items?.type != 'preview'" style="min-width: 70px; text-align: center;">
                              <i v-if="answer?.is_correct_answer == true" style="font-size: 50px" class="far fa-check-circle"></i>
                              <i v-else-if="answer?.is_correct_answer == false" style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                            </div>
                            <div :class="'d-flex align-content-center ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')">
                              <span style="font-size: 20px;" v-html="answer.answer"></span>
                              <img v-if="question?.sub_questions[0].voice_for_answer == 1 && answer.answer_play_audio === 0" class="ml-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, 0, keyAnswer)">
                              <img v-if="question?.sub_questions[0].voice_for_answer == 1 && answer.answer_play_audio === 1" class="ml-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, 0, keyAnswer)">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div v-else-if="question.main_type == 4" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 0" class="mb-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                        <div v-if="question.content_main_audio" class="mb-3" style="text-align: center;">
                          <div>
                            <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.content_main_audio" type="audio/mpeg">
                            </audio>
                            <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                          </div>
                        </div>
                        <div v-for="(content, keyContent) in question.contents" class="w-100 mt-3" style="font-weight: normal">
                          <div style="display: flex;">
                            <div style="align-items: center; width: 100%;" v-html="content?.template"></div>
                          </div>
                          <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                            <p>Correct</p>
                            <p style="padding-left: 10px; margin-bottom: 0;" v-html="content?.complete_answer"></p>
                          </div>
                        </div>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 1" class="mt-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                      </div>
                    </div>

                    <div v-else-if="question.main_type == 5" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 0" class="mb-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                        <div v-if="question.content_main_audio" class="mb-3" style="text-align: center;">
                          <div>
                            <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.content_main_audio" type="audio/mpeg">
                            </audio>
                            <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                          </div>
                        </div>
                        <div v-if="question.flag_show_dropdown == 1" class="d-flex justify-content-center" style="font-weight: normal">
                          <table class="table table-bordered">
                            <tbody>
                              <tr v-for="(option, keyOption) in question.options">
                                <td>@{{ option.content }}</td>
                                <td>@{{ option.description }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div v-for="(content, keyContent) in question.contents" class="w-100 mt-3" style="font-weight: normal">
                          <div style="display: flex;">
                            <div style="align-items: center; width: 100%;" v-html="content?.template"></div>
                          </div>
                          <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                            <p>Correct</p>
                            <p style="padding-left: 10px; margin-bottom: 0;" v-html="content?.complete_answer"></p>
                          </div>
                        </div>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 1" class="mt-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else-if="items.topic_test_type == 'IELTS_LISTENING'" id="ielts_listening_container" style="margin-top: 45px;">
    <div v-if="items.sections" v-for="(section, keySection) in items.sections">
      <div class="row steps-fields" style="margin-left: 0; margin-right: 0;">
        <div class="card col-sm-12 card-question accordion" style="width: 18rem;" id="section_steps">
          <div :id="'s' + (keySection) + '_edit-step'" :class="'card-body collapse s_collapse ' + (keySection == 0 ? 'show' : '')" style="font-weight: bold;" aria-expanded="false" aria-labelledby="headingOne" data-parent="#section_steps">
            <h5 class="card-title section_name" style="margin-left: -15px !important;">
              @{{ section.section_name }}
            </h5>
            <div v-if="section?.audio" class="buttons text-center p-3">
              <audio v-if="section?.audio" class="section-main-audio audio-qa listening_section_audio" :id="'section_main_audio'+ section.section_id" v-on:click.prevent="playAudioSection(section.section_id)" controls preload>
                <source :src="section.audio" type="audio/mpeg">
              </audio>
            </div>
            <div class="row">
              <div class="col-xs-12 box_question" :style="'width: 100%; overflow-y: scroll; border: 1px solid #51595C; border-radius: 5px; padding: 10px; height: ' + passageAndQuestionBoxHeight + 'px'">
                <div v-if="section?.questions" v-for="(question, keyQuestion) in section.questions" style="margin-bottom: 20px;">
                  <div class="row" style="margin-left: 0; margin-right: 0;">
                    <div v-if="question.main_type == 1" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div drag-parent-id="container-answer-sort" style="margin-bottom: 50px; font-size: 16px; font-weight: normal">
                          <div v-if="(question.content_main_picture && question.picture_bellow_text == 0) || question.content_main_audio" style="margin-bottom: 50px; text-align: center;">
                            <img v-if="question.content_main_picture && question.picture_bellow_text == 0" style="max-width: 600px; max-height: 300px;" v-if="question.content_main_picture" :src="question.content_main_picture" alt="question_img">
                            <br />
                            <br />
                            <div v-if="question.content_main_audio">
                              <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                                <source :src="question.content_main_audio" type="audio/mpeg">
                              </audio>
                              <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                              <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            </div>
                          </div>
                          <div v-if="question.category == 3" v-for="(content, keyContent) in question.contents">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <div style="display: flex; align-items: center; margin-bottom: 20px;" :id="'question-' + (question.question_id) + '-content'">
                              <div v-if="items?.isResult && content?.is_correct_answer != 'none' && items?.type != 'preview'" style="padding-right: 20px; min-width: 70px">
                                <i v-if="content?.is_correct_answer == true" style="font-size: 50px; color: #7BBB44;" class="fas fa-check-circle"></i>
                                <i v-else style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                              </div>
                              <div style="white-space: pre-wrap;" v-html="content?.template"></div>
                            </div>
                            <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                              <p>Correct</p>
                              <p style="padding-left: 10px; margin-bottom: 0; white-space: pre-wrap;" v-html="content?.complete_answer"></p>
                            </div>
                          </div>

                          <div v-else v-for="(content, keyContent) in question.contents">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <div style="display: flex; align-items: center; margin-bottom: 20px;" :id="'question-' + (question.question_id) + '-content'">
                              <div style="white-space: pre-wrap" v-html="content?.template"></div>
                            </div>
                          </div>

                          <div v-if="question.content_main_picture && question.picture_bellow_text == 1" style="margin-top: 20px; margin-bottom: 20px; text-align: center;">
                            <img style="max-width: 600px; max-height: 300px;" v-if="question.content_main_picture" :src="question.content_main_picture" alt="question_img">
                          </div>
                        </div>

                        <div v-if="!items?.isResult && items?.type != 'preview'" style="display: flex; flex-flow: row wrap; font-weight: normal" drag-parent-id="container-template-sort">
                          <div v-for="(answer, keyAnswer) in question.shuffle_answer" v-if="answer.answer" :id="'question-' + (question.question_id) + '-drag' + (keyAnswer + 1)" class="container_drag mt-1 style_template" draggable="true" style="cursor: move !important;">
                            <span style="cursor: move !important;" v-html="answer.answer"></span>
                            <img v-if="question.voice_for_answer == 1" :class="'icon_sort-question' + (question.question_id) + '_answer' + (keyAnswer+1)" style="width: 30px; cursor: pointer; display: inline-block" onclick="textToSpeechBySort(event, false)" draggable="false" src="/img/speaker.png" alt="speaker" :speaker-section-id="section.section_id" :speaker-question-id="question.question_id" :speaker-key-answer="keyAnswer">
                            <img v-if="question.voice_for_answer == 1" :class="'icon_gif_sort-question' + (question.question_id) + '_answer' + (keyAnswer+1)" style="width: 30px; cursor: pointer; display: none" onclick="textToSpeechBySort(event, false)" draggable="false" src="/img/speaker_animated.gif" alt="speaker" :speaker-section-id="section.section_id" :speaker-question-id="question.question_id" :speaker-key-answer="keyAnswer">
                          </div>
                        </div>
                      </div>
                    </div>

                    <div v-else-if="question.main_type == 2 && question.sub_questions.length == 1" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" :id="'question-' + (question.question_id)" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div style="margin-bottom: 50px; text-align: center; width: 100%; font-weight: normal">
                          <img v-if="question?.content_main_picture && question?.picture_bellow_text == 0" style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                          <div v-if="question?.content_main_text" class="mt-1 mb-1 text-left">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                          </div>
                          <div style="text-align: left; white-space: pre-wrap;" v-if="question?.content_main_text" :id="'question-' + (question.question_id) + '-content'" v-html="question?.content_main_text"></div>
                          <div style="text-align: left; white-space: pre-wrap;" v-if="question?.sub_questions[0].sub_question">
                            <span v-html="question?.sub_questions[0].sub_question"></span>
                            <img v-if="question?.sub_questions[0].voice_for_sub_question == 1 && question?.sub_questions[0].subQ_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, 0)">
                            <img v-if="question?.sub_questions[0].voice_for_sub_question == 1 && question?.sub_questions[0].subQ_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, 0)">
                          </div>
                          <img v-if="question?.content_main_picture && question?.picture_bellow_text == 1" style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                          <div v-if="question?.content_main_audio">
                            <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.content_main_audio" type="audio/mpeg">
                            </audio>
                            <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                          </div>
                        </div>
                        <div :id="'q' + (question.question_id) + '_edit-step1'" style="font-weight: normal">
                          <div class="align-content-center qa-answer-hover cursor-hover" v-for="(answer, keyAnswer) in question.sub_questions[0].shuffle_answer" v-on:click.prevent="clickAnswer(items?.isResult, question.question_id, 0, keyAnswer, question.sub_questions[0].correct_answer.length, section.section_id)" style="display: inline-flex; width: 41%; margin-left: 6%;" :class="'qna_answer_'+ section.section_id + '_'+question.question_id + '_sub_q0' + ' ' + 'qa_answer q' + (question.question_id) + '_edit-step1_answer ' + 'q' + (question.question_id) + '_edit-step1_answer' + (keyAnswer+1) + ' ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'choose_answer' : '')">
                            <div v-if="!items?.isResult || answer?.answer_to_choose == true || items?.type == 'preview' || (answer?.is_correct_answer == 'none' && answer?.answer_to_choose == 'none')" :class="(items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')" style="cursor: pointer;color: #7BBB44; font-size: 30px; margin-right: 10px; min-width: 70px; text-align: center;" v-html="keyAnswer+1"></div>
                            <div v-if="items?.isResult && answer?.is_correct_answer != 'none' && items?.type != 'preview'" style="min-width: 70px; text-align: center;">
                              <i v-if="answer?.is_correct_answer == true" style="font-size: 50px" class="far fa-check-circle"></i>
                              <i v-else-if="answer?.is_correct_answer == false" style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                            </div>
                            <div :class="'d-flex align-content-center ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')">
                              <span style="font-size: 20px;" v-html="answer.answer"></span>
                              <img v-if="question?.sub_questions[0].voice_for_answer == 1 && answer.answer_play_audio === 0" class="ml-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, 0, keyAnswer)">
                              <img v-if="question?.sub_questions[0].voice_for_answer == 1 && answer.answer_play_audio === 1" class="ml-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, 0, keyAnswer)">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div v-else-if="question.main_type == 4" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 0" class="mb-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                        <div v-if="question.content_main_audio" class="mb-3" style="text-align: center;">
                          <div>
                            <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.content_main_audio" type="audio/mpeg">
                            </audio>
                            <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                          </div>
                        </div>
                        <div v-for="(content, keyContent) in question.contents" class="w-100 mt-3" style="font-weight: normal">
                          <div style="display: flex;">
                            <div style="align-items: center; width: 100%;" v-html="content?.template"></div>
                          </div>
                          <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                            <p>Correct</p>
                            <p style="padding-left: 10px; margin-bottom: 0;" v-html="content?.complete_answer"></p>
                          </div>
                        </div>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 1" class="mt-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                      </div>
                    </div>

                    <div v-else-if="question.main_type == 5" class="card col-sm-12 card-question" style="width: 18rem;">
                      <div class="card-body" style="font-weight: bold;">
                        <h5 class="card-title">
                          @{{ (keyQuestion+1) + '. ' + question.title }}
                          <div v-if="question.audio_title" class="d-inline-block">
                            <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.audio_title" type="audio/mpeg">
                            </audio>
                            <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                            <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                          </div>
                          <div v-else class="d-inline-block">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                            <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                          </div>
                        </h5>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 0" class="mb-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                        <div v-if="question.content_main_audio" class="mb-3" style="text-align: center;">
                          <div>
                            <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                              <source :src="question.content_main_audio" type="audio/mpeg">
                            </audio>
                            <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                            <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                          </div>
                        </div>
                        <div v-if="question.flag_show_dropdown == 1" class="d-flex justify-content-center" style="font-weight: normal">
                          <table class="table table-bordered">
                            <tbody>
                              <tr v-for="(option, keyOption) in question.options">
                                <td>@{{ option.content }}</td>
                                <td>@{{ option.description }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div v-for="(content, keyContent) in question.contents" class="w-100 mt-3" style="font-weight: normal">
                          <div style="display: flex;">
                            <div style="align-items: center; width: 100%;" v-html="content?.template"></div>
                          </div>
                          <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                            <p>Correct</p>
                            <p style="padding-left: 10px; margin-bottom: 0;" v-html="content?.complete_answer"></p>
                          </div>
                        </div>
                        <div v-if="question.content_main_picture && question.picture_bellow_text == 1" class="mt-3" style="text-align: center;">
                          <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else style="margin-bottom: 20px; padding-top: 65px">
    <div v-for="(section, keySection) in items.sections">
      <div v-if="section.questions" v-for="(question, keyQuestion) in section.questions" :style="(keyQuestion == 0 && !items?.isResult) ? 'margin-top: 50px' : ''">
        <div v-if="items.topic_test_type == 'IELTS_WRITING'" class="row" style="margin-left: 0; margin-right: 0;">
          <div class="card col-sm-12 card-question" style="width: 18rem;">
            <div class="card-body" style="font-weight: bold;">
              <h5 class="card-title">
                @{{ (keyQuestion+1) + '. ' + question.title }}
                <div v-if="question.audio_title" class="d-inline-block">
                  <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                    <source :src="question.audio_title" type="audio/mpeg">
                  </audio>
                  <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                  <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                </div>
                <div v-else class="d-inline-block">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                </div>
              </h5>
              <div class="pl-3 pr-3" style="text-align: left; white-space: pre-wrap; font-weight: normal" v-if="question?.content_main_text" :id="'question-' + (question.question_id) + '-content'" v-html="question?.content_main_text"></div>
              <div v-if="question.word_minimum" class="mt-3" style="text-align: left; color: red; font-weight: normal">
                Write at least <span v-html="question?.word_minimum"></span> words.
              </div>
              <div class="form-group mt-3" style="font-weight: normal">
                <label :for="'question-' + (question.question_id) + '-answer'">Answer</label>
                <textarea class="form-control" :id="'question-' + (question.question_id) + '-answer'" v-on:keyup="wordCountOfTheAnswer(question.question_id)" rows="3" style="height: 250px;" v-html="items?.isResult && question.answer || ''" :disabled="items?.isResult"></textarea>
              </div>
              <div :id="'question-' + (question.question_id) + '-error_message'" style="font-weight: normal"></div>
              <div class="mt-3" style="font-weight: normal">
                Words Count: <span :id="'question-' + (question.question_id) + '-show_word_count'" v-html="items?.isResult && question.word_count || 0">0</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="row" style="margin-left: 0; margin-right: 0;">
          <div v-if="question.main_type == 1" class="card col-sm-12 card-question" style="width: 18rem;">
            <div class="card-body" style="font-weight: bold;">
              <h5 class="card-title">
                @{{ (keyQuestion+1) + '. ' + question.title }}
                <div v-if="question.audio_title" class="d-inline-block">
                  <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                    <source :src="question.audio_title" type="audio/mpeg">
                  </audio>
                  <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                  <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                </div>
                <div v-else class="d-inline-block">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                </div>
              </h5>
              <div drag-parent-id="container-answer-sort" style="margin-bottom: 50px; font-size: 16px; font-weight: normal">
                <div v-if="(question.content_main_picture && question.picture_bellow_text == 0) || question.content_main_audio" style="margin-bottom: 50px; text-align: center;">
                  <img v-if="question.content_main_picture && question.picture_bellow_text == 0" style="max-width: 600px; max-height: 300px;" v-if="question.content_main_picture" :src="question.content_main_picture" alt="question_img">
                  <br />
                  <br />
                  <div v-if="question.content_main_audio">
                    <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                      <source :src="question.content_main_audio" type="audio/mpeg">
                    </audio>
                    <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                    <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                  </div>
                </div>

                <div v-if="question.category == 3" v-for="(content, keyContent) in question.contents">
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                  <div style="display: flex; align-items: center; margin-bottom: 20px;" :id="'question-' + (question.question_id) + '-content'">
                    <div v-if="items?.isResult && content?.is_correct_answer != 'none' && items?.type != 'preview'" style="padding-right: 20px; min-width: 70px">
                      <i v-if="content?.is_correct_answer == true" style="font-size: 50px; color: #7BBB44;" class="fas fa-check-circle"></i>
                      <i v-else style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                    </div>
                    <div style="white-space: pre-wrap;" v-html="content?.template"></div>
                  </div>
                  <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                    <p>Correct</p>
                    <p style="padding-left: 10px; margin-bottom: 0; white-space: pre-wrap;" v-html="content?.complete_answer"></p>
                  </div>
                </div>

                <div v-else v-for="(content, keyContent) in question.contents">
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                  <div style="display: flex; align-items: center; margin-bottom: 20px;" :id="'question-' + (question.question_id) + '-content'">
                    <div style="white-space: pre-wrap" v-html="content?.template"></div>
                  </div>
                </div>

                <div v-if="question.content_main_picture && question.picture_bellow_text == 1" style="margin-top: 20px; margin-bottom: 20px; text-align: center;">
                  <img style="max-width: 600px; max-height: 300px;" v-if="question.content_main_picture" :src="question.content_main_picture" alt="question_img">
                </div>
              </div>

              <div v-if="!items?.isResult && items?.type != 'preview'" style="display: flex; flex-flow: row wrap; font-weight: normal" drag-parent-id="container-template-sort">
                <div v-for="(answer, keyAnswer) in question.shuffle_answer" v-if="answer.answer" :id="'question-' + (question.question_id) + '-drag' + (keyAnswer + 1)" class="container_drag mt-1 style_template" draggable="true" style="cursor: move !important;">
                  <span style="cursor: move !important;" v-html="answer.answer"></span>
                  <img v-if="question.voice_for_answer == 1" :class="'icon_sort-question' + (question.question_id) + '_answer' + (keyAnswer+1)" style="width: 30px; cursor: pointer; display: inline-block" onclick="textToSpeechBySort(event, false)" draggable="false" src="/img/speaker.png" alt="speaker" :speaker-section-id="section.section_id" :speaker-question-id="question.question_id" :speaker-key-answer="keyAnswer">
                  <img v-if="question.voice_for_answer == 1" :class="'icon_gif_sort-question' + (question.question_id) + '_answer' + (keyAnswer+1)" style="width: 30px; cursor: pointer; display: none" onclick="textToSpeechBySort(event, false)" draggable="false" src="/img/speaker_animated.gif" alt="speaker" :speaker-section-id="section.section_id" :speaker-question-id="question.question_id" :speaker-key-answer="keyAnswer">
                </div>
              </div>
            </div>
          </div>
          <div v-else-if="question.main_type == 2 && question.sub_questions.length > 1" class="card col-sm-12 card-question" style="width: 18rem;">
            <div class="card-body" :id="'question-' + (question.question_id)" style="font-weight: bold;">
              <h5 class="card-title">
                @{{ (keyQuestion+1) + '. ' + question.title }}
                <div v-if="question.audio_title" class="d-inline-block">
                  <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                    <source :src="question.audio_title" type="audio/mpeg">
                  </audio>
                  <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                  <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                </div>
                <div v-else class="d-inline-block">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                </div>
              </h5>
              <div class="row" style="font-weight: normal">
                <div class="col-xs-12 col-md-7" style="width: 100%; height: 700px; overflow-y: scroll; border: 3px solid #51595C; border-radius: 5px; padding: 10px">
                  <div v-if="question?.content_main_picture && (question?.picture_bellow_text == 0 || !question?.content_main_text)" :class="'w-100 text-center ' + (question?.content_main_picture && !question?.content_main_text && !question?.content_main_audio ? ' d-flex h-100 align-items-center justify-content-center ': '')">
                    <img style="max-width: 100%;" :src="question.content_main_picture" alt="question_img">
                  </div>
                  <div v-if="question?.content_main_audio" class="buttons text-center p-3">
                    <audio v-if="question?.content_main_audio" class="question-main-audio audio-qa" :id="'question_main_audio'+ question.question_id" v-on:click.prevent="playAudio(section.section_id, question.question_id)" controls preload>
                      <source :src="question.content_main_audio" type="audio/mpeg">
                    </audio>
                  </div>
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0 && question?.content_main_text" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1 && question?.content_main_text" class="mt-1 mb-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                  <div v-if="question?.content_main_text" :id="'question-' + (question.question_id) + '-content'" style="white-space: pre-wrap" v-html="question?.content_main_text"></div>
                  <div v-if="question?.content_main_picture && question?.picture_bellow_text == 1 && question?.content_main_text" class="w-100 text-center">
                    <img style="max-width: 100%;" :src="question.content_main_picture" alt="question_img">
                  </div>
                </div>
                <div class="col-xs-12 col-md-5">
                  <div style="width: 100%; min-height: 200px; border: 3px solid #51595C; border-radius: 5px; margin-bottom: 30px;">
                    <div class="steps-progress py-3">
                      <div v-if="items?.isResult && items?.type != 'preview'" class="container d-flex align-items-center justify-content-between">
                        <div v-for="(subQ, keySubQ) in question.sub_questions" :class="'d-flex '">
                          <div v-if="items?.isResult">
                            <i v-if="subQ?.result_status == true" style="font-size: 40px; color: #7BBB44;" class="far fa-check-circle"></i>
                            <i v-else style="font-size: 40px; color: #FF0000;" class="far fa-times-circle"></i>
                          </div>
                        </div>
                      </div>
                      <div class="container d-flex align-items-center justify-content-between">
                        <div v-for="(subQ, keySubQ) in question.sub_questions" :class="'d-flex align-items-center ' + (keySubQ < (question.sub_questions.length - 1) ? 'w-100' : '')">
                          <div>
                            <button :class="'btn btn-sm btn-step-progress q' + (question.question_id) + '_btn-step-progress ' + (keySubQ == 0 ? 'style_active' : '')" v-on:click.prevent="setIdxEditStepForQAQuestion(section.section_id, question.question_id, keySubQ)" type="button" aria-expanded="false" :data-target="'#q' + (question.question_id) + '_edit-step' + (keySubQ+1)" :aria-controls="'q' + (question.question_id) + '_edit-step' + (keySubQ+1)" v-html="keySubQ+1"></button>
                          </div>
                          <div v-if="keySubQ < (question.sub_questions.length - 1)" class="w-100">
                            <hr style="border-top: 3px solid #7D9195; width: 100%">
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="steps-fields pt-3">
                      <div class="container">
                        <form>
                          <div class="accordion" id="steps">
                            <div v-for="(subQ, keySubQ) in question.sub_questions" :id="'q' + (question.question_id) + '_edit-step' + (keySubQ+1)" aria-expanded="false" :class="'collapse q' + (question.question_id) + '_collapse ' + (keySubQ == 0 ? 'show' : '')" aria-labelledby="headingOne" data-parent="#steps">
                              <div style="margin-bottom: 20px;">
                                <span v-html="subQ?.sub_question"></span>
                                <img v-if="subQ.voice_for_sub_question == 1 && subQ.subQ_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, keySubQ)">
                                <img v-if="subQ.voice_for_sub_question == 1 && subQ.subQ_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, keySubQ)">
                              </div>
                              <div class="bg-white">
                                <div class="row">
                                  <div class="form-group col-12">
                                    <div v-for="(answer, keyAnswer) in subQ.shuffle_answer" v-on:click.prevent="clickAnswer(items?.isResult, question.question_id, keySubQ, keyAnswer, question.sub_questions[keySubQ].correct_answer.length, section.section_id)" :class="'qna_answer_'+ section.section_id + '_'+question.question_id + '_sub_q' + keySubQ + ' ' + 'qa_answer qa-answer-hover cursor-hover q' + (question.question_id) + '_edit-step' + (keySubQ+1) + '_answer ' + 'q' + (question.question_id) + '_edit-step' + (keySubQ+1) + '_answer' + (keyAnswer+1) + ' ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'choose_answer' : '')">
                                      <div v-if="!items?.isResult || answer?.answer_to_choose == true || items?.type == 'preview' || (answer?.is_correct_answer == 'none' && answer?.answer_to_choose == 'none')" :class="'number_question ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')" style="cursor: pointer;min-width: 70px; text-align: center;" v-html="keyAnswer+1"></div>
                                      <div v-if="items?.isResult && answer?.is_correct_answer != 'none' && items?.type != 'preview'" style="cursor: pointer;min-width: 70px; text-align: center;">
                                        <i v-if="answer?.is_correct_answer == true" style="font-size: 50px" class="far fa-check-circle"></i>
                                        <i v-else-if="answer?.is_correct_answer == false" style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                                      </div>
                                      <div>
                                        <span :class="(items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')" style="font-size: 20px;cursor: pointer" v-html="answer.answer"></span>
                                        <img v-if="subQ.voice_for_answer == 1 && answer.answer_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, keySubQ, keyAnswer)">
                                        <img v-if="subQ.voice_for_answer == 1 && answer.answer_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, keySubQ, keyAnswer)">
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <div style="text-align: center;">
                    <button style="color: white; font-weight: bold; display: none;" type="button" :class="'btn btn-warning q' + (question.question_id) + '-btn-pre-qa'" v-on:click.prevent="preQA(section.section_id, question.question_id)">Câu Trước</button>
                    <button style="margin-left: 20px; color: white; font-weight: bold;" type="button" :class="'btn btn-warning q' + (question.question_id) + '-btn-next-qa'" v-on:click.prevent="nextQA(section.section_id, question.question_id)">Câu tiếp</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="question.main_type == 2 && question.sub_questions.length == 1" class="card col-sm-12 card-question" style="width: 18rem;">
            <div class="card-body" :id="'question-' + (question.question_id)" style="font-weight: bold;">
              <h5 class="card-title">
                @{{ (keyQuestion+1) + '. ' + question.title }}
                <div v-if="question.audio_title" class="d-inline-block">
                  <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                    <source :src="question.audio_title" type="audio/mpeg">
                  </audio>
                  <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                  <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                </div>
                <div v-else class="d-inline-block">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                </div>
              </h5>
              <div style="margin-bottom: 50px; text-align: center; width: 100%; font-weight: normal">
                <img v-if="question?.content_main_picture && question?.picture_bellow_text == 0" style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                <div v-if="question?.content_main_text" class="mt-1 mb-1 text-left">
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_content_main == 1 && question.content_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playContentAudio(section.section_id, question.question_id)">
                </div>
                <div style="text-align: left; white-space: pre-wrap;" v-if="question?.content_main_text" :id="'question-' + (question.question_id) + '-content'" v-html="question?.content_main_text"></div>
                <div style="text-align: left; white-space: pre-wrap;" v-if="question?.sub_questions[0].sub_question">
                  <span v-html="question?.sub_questions[0].sub_question"></span>
                  <img v-if="question?.sub_questions[0].voice_for_sub_question == 1 && question?.sub_questions[0].subQ_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, 0)">
                  <img v-if="question?.sub_questions[0].voice_for_sub_question == 1 && question?.sub_questions[0].subQ_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playSubQuestionAudio(section.section_id, question.question_id, 0)">
                </div>
                <img v-if="question?.content_main_picture && question?.picture_bellow_text == 1" style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
                <div v-if="question?.content_main_audio">
                  <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                    <source :src="question.content_main_audio" type="audio/mpeg">
                  </audio>
                  <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                  <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                </div>
              </div>
              <div :id="'q' + (question.question_id) + '_edit-step1'" style="font-weight: normal">
                <div class="align-content-center qa-answer-hover cursor-hover" v-for="(answer, keyAnswer) in question.sub_questions[0].shuffle_answer" v-on:click.prevent="clickAnswer(items?.isResult, question.question_id, 0, keyAnswer, question.sub_questions[0].correct_answer.length, section.section_id)" style="display: inline-flex; width: 41%; margin-left: 6%;" :class="'qna_answer_' + section.section_id + '_'+question.question_id + '_sub_q0' +  ' ' + 'qa_answer q' + (question.question_id) + '_edit-step1_answer ' + 'q' + (question.question_id) + '_edit-step1_answer' + (keyAnswer+1) + ' ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'choose_answer' : '')">
                  <!-- <div class="d-flex w-100 align-content-center"> -->
                  <div v-if="!items?.isResult || answer?.answer_to_choose == true || items?.type == 'preview' || (answer?.is_correct_answer == 'none' && answer?.answer_to_choose == 'none')" :class="(items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')" style="cursor: pointer;color: #7BBB44; font-size: 30px; margin-right: 10px; min-width: 70px; text-align: center;" v-html="keyAnswer+1"></div>
                  <div v-if="items?.isResult && answer?.is_correct_answer != 'none' && items?.type != 'preview'" style="min-width: 70px; text-align: center;">
                    <i v-if="answer?.is_correct_answer == true" style="font-size: 50px" class="far fa-check-circle"></i>
                    <i v-else-if="answer?.is_correct_answer == false" style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                  </div>
                  <div :class="'d-flex align-content-center ' + (items?.isResult && (answer.is_correct_answer == true || answer.answer_to_choose == true) ? 'text-white' : '')">
                    <span style="font-size: 20px;" v-html="answer.answer"></span>
                    <img v-if="question?.sub_questions[0].voice_for_answer == 1 && answer.answer_play_audio === 0" class="ml-1" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, 0, keyAnswer)">
                    <img v-if="question?.sub_questions[0].voice_for_answer == 1 && answer.answer_play_audio === 1" class="ml-1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playAnswerSubQuestionAudio(section.section_id, question.question_id, 0, keyAnswer)">
                  </div>
                  <!-- </div> -->
                </div>
              </div>
            </div>
          </div>
          <div v-else-if="question.main_type == 3" :id="'page_connections_' + (question.question_id)" style="width: 100%;" v-on:mouseleave="removeItemActiveHover">
            <div class="row" style="margin-left: 0; margin-right: 0;">
              <div class="card col-sm-12 card-question" style="width: 18rem;">
                <div class="card-body" :id="'question-' + (question.question_id)" style="font-weight: bold; position: relative">
                  <h5 class="card-title">
                    @{{ (keyQuestion+1) + '. ' + question.title }}
                    <div v-if="question.audio_title" class="d-inline-block">
                      <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                        <source :src="question.audio_title" type="audio/mpeg">
                      </audio>
                      <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                      <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                    </div>
                    <div v-else class="d-inline-block">
                      <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                      <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                    </div>
                  </h5>
                  <div class="d-flex justify-content-start" style="margin-bottom: 150px; font-weight: normal">
                    <div v-for="(valRow1, keyRow1) in question.shuffle_row_1" style="width: 18%; margin-right: 2%;">
                      <div class="d-flex justify-content-center align-items-center cursor-hover match-choose" v-on:click.prevent="sourceClickHandler(items?.isResult, question.question_id, 'q' + (question.question_id) + '-row1' + '_matching' + (keyRow1 + 1), keyRow1)" v-if="valRow1.picture_url_a" :drag-content="replaceAllSpecialChar(valRow1.picture_url_a)" :id="'q' + (question.question_id) + '-row1' + '_matching' + (keyRow1 + 1)" style="width: 100%; height: 250px; border: 4px solid #7D9195;">
                        <img style="max-width: 100%; max-height: 100%;" :src="valRow1.picture_url_a" alt="image">
                      </div>
                      <div class="cursor-hover match-choose" v-on:click.prevent="sourceClickHandler(items?.isResult, question.question_id, 'q' + (question.question_id) + '-row1' + '_matching' + (keyRow1 + 1), keyRow1)" v-else-if="valRow1.content_text_a" :drag-content="replaceAllSpecialChar(valRow1.content_text_a)" :id="'q' + (question.question_id) + '-row1' + '_matching' + (keyRow1 + 1)" style="display: inline-block; min-height: 50px; min-width: 20px; border: 4px solid #7D9195; margin-right: 20px; padding: 5px 10px; border-radius: 10px; text-align: center; width: 100%; height: 100%">
                        <div v-html="valRow1.content_text_a"></div>
                        <img v-if="valRow1.voice_for_content_text_a && valRow1.play_audio === 0" style="width: 30px;" src="/img/speaker.png" alt="speaker" @click="playAudioMatchRow1(section.section_id, question.question_id, keyRow1)">
                        <img v-if="valRow1.voice_for_content_text_a && valRow1.play_audio === 1" style="width: 30px;" src="/img/speaker_animated.gif" alt="speaker" @click="playAudioMatchRow1(section.section_id, question.question_id, keyRow1)">
                      </div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-start" style="font-weight: normal">
                    <div v-for="(valRow2, keyRow2) in question.shuffle_row_2" style="width: 18%; margin-right: 2%">
                      <div class="d-flex justify-content-center align-items-center cursor-hover match-choose" v-on:click.prevent="targetEndpoint(items?.isResult, question.question_id, 'q' + (question.question_id) + '-row2' + '_matching' + (keyRow2 + 1), keyRow2)" :drag-content="replaceAllSpecialChar(valRow2.picture_url_b)" v-if="valRow2.picture_url_b" :id="'q' + (question.question_id) + '-row2' + '_matching' + (keyRow2 + 1)" style="width: 100%; height: 250px; border: 4px solid #7D9195;">
                        <img style="max-width: 100%; max-height: 100%;" :src="valRow2.picture_url_b" alt="image">
                      </div>
                      <div :id="'q' + (question.question_id) + '-row2' + '_matching' + (keyRow2 + 1)" v-if="valRow2.audio_url && !valRow2.content_text_b" :drag-content="replaceAllSpecialChar(valRow2.audio_url)" class="text-center cursor-hover match-choose" v-on:click.prevent="targetEndpoint(items?.isResult, question.question_id, 'q' + (question.question_id) + '-row2' + '_matching' + (keyRow2 + 1), keyRow2)" style=" min-height: 50px; min-width: 20px; padding: 5px 10px; border-radius: 10px; text-align: center; width: 100%; height: 100%" :style="(!valRow2.content_text_b && !valRow2.picture_url_b) ? 'border: 4px solid #7D9195; margin-right: 20px' : 'border: unset !important'">
                        <audio class="row2-match-audio" :id="'row2_audio_match'+ (question.question_id) + '_' + (keyRow2+1)" @ended="enderAudioMatch(section.section_id, question.question_id, keyRow2)" preload hidden>
                          <source :src="valRow2.audio_url" type="audio/mpeg">
                        </audio>
                        <img v-if="valRow2.play_audio === 0" style="width: 30px;" src="/img/speaker.png" alt="speaker" @click="playAudioMatchRow2(section.section_id, question.question_id, keyRow2)">
                        <img v-if="valRow2.play_audio === 1" style="width: 30px;" src="/img/speaker_animated.gif" alt="speaker" @click="playAudioMatchRow2(section.section_id, question.question_id, keyRow2)">
                      </div>
                      <div class="cursor-hover match-choose" v-else-if="valRow2.content_text_b" :drag-content="replaceAllSpecialChar(valRow2.content_text_b)" :id="'q' + (question.question_id) + '-row2' + '_matching' + (keyRow2 + 1)" v-on:click.prevent="targetEndpoint(items?.isResult, question.question_id, 'q' + (question.question_id) + '-row2' + '_matching' + (keyRow2 + 1), keyRow2)" style="display: inline-block; min-height: 50px; min-width: 20px; border: 4px solid #7D9195; margin-right: 20px; padding: 5px 10px; border-radius: 10px; text-align: center; width: 100%; height: 100%">
                        <div v-html="valRow2.content_text_b"></div>
                        <audio class="row2-match-audio" :id="'row2_audio_match'+ (question.question_id) + '_' + (keyRow2+1)" @ended="enderAudioMatch(section.section_id, question.question_id, keyRow2)" preload hidden>
                          <source :src="valRow2.audio_url" type="audio/mpeg">
                        </audio>
                        <img v-if="valRow2.play_audio === 0" style="width: 30px;" src="/img/speaker.png" alt="speaker" @click="playAudioMatchRow2(section.section_id, question.question_id, keyRow2)">
                        <img v-if="valRow2.play_audio === 1" style="width: 30px;" src="/img/speaker_animated.gif" alt="speaker" @click="playAudioMatchRow2(section.section_id, question.question_id, keyRow2)">
                      </div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-start" style="font-weight: normal">
                    <div v-for="(valRow2, keyRow2) in question.shuffle_row_2" v-if="items?.isResult && valRow2?.is_correct_answer != 'none' && items?.type != 'preview'" :class="'q' + (question.question_id) + '-icon-result-matching'" style="width: 18%; margin-right: 2%; padding-top: 10px; min-width: 70px">
                      <i v-if="valRow2?.is_correct_answer" style="font-size: 50px; color: #7BBB44;" class="fas fa-check-circle"></i>
                      <i v-else style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
                    </div>
                  </div>
                  <div v-if="items?.isResult && items?.type != 'preview'" class="text-center mt-5">
                    <button type="button" :class="'btn btn-warning text-white ' + 'btn-show-matching-result-q' + (question.question_id)" v-on:click.prevent="showMatchingResult(question.question_id)">Xem đáp án</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="question.main_type == 4" class="card col-sm-12 card-question" style="width: 18rem;">
            <div class="card-body" style="font-weight: bold;">
              <h5 class="card-title">
                @{{ (keyQuestion+1) + '. ' + question.title }}
                <div v-if="question.audio_title" class="d-inline-block">
                  <audio class="question-main-audio" :id="'question_title_audio'+ question.question_id" @ended="enderAudioTitle(section.section_id, question.question_id)" preload hidden>
                    <source :src="question.audio_title" type="audio/mpeg">
                  </audio>
                  <img v-if="question.title_play_audio_with_url === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                  <img v-if="question.title_play_audio_with_url === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id, 'audio_link')">
                </div>
                <div v-else class="d-inline-block">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 0" style="width: 30px; cursor: pointer;" src="/img/speaker.png" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                  <img v-if="question.voice_for_title == 1 && question.title_play_audio === 1" style="width: 30px; cursor: pointer;" src="/img/speaker_animated.gif" alt="speaker" @click="playTitleAudio(section.section_id, question.question_id)">
                </div>
              </h5>
              <div v-if="question.content_main_picture && question.picture_bellow_text == 0" class="mb-3" style="text-align: center;">
                <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
              </div>
              <div v-if="question.content_main_audio" class="mb-3" style="text-align: center;">
                <div>
                  <audio class="question-main-audio" :id="'question_main_audio'+ question.question_id" @ended="enderAudio(section.section_id, question.question_id)" preload hidden>
                    <source :src="question.content_main_audio" type="audio/mpeg">
                  </audio>
                  <i v-if="question.play_audio === 0" class="fas fa-play-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                  <i v-if="question.play_audio === 1" class="fas fa-stop-circle icon-style" v-on:click.prevent="playAudio(section.section_id, question.question_id)"></i>
                </div>
              </div>
              <!-- <div v-if="question.content_main_text" style="text-align: left;" v-html="question.content_main_text"></div> -->
              <div v-for="(content, keyContent) in question.contents" class="w-100 mt-3" style="font-weight: normal">
                <div style="display: flex;">
                  <!-- <div v-if="items?.isResult && content?.is_correct_answer != 'none' && items?.type != 'preview'" style="padding-right: 20px; min-width: 70px">
              <i v-if="content?.is_correct_answer == true" style="font-size: 50px; color: #7BBB44;" class="fas fa-check-circle"></i>
              <i v-else-if="content?.is_correct_answer == false" style="font-size: 50px; color: #FF0000;" class="far fa-times-circle"></i>
            </div> -->
                  <div style="align-items: center; width: 100%; font-weight: normal" v-html="content?.template"></div>
                </div>
                <div v-if="items?.isResult && content?.is_correct_answer == false && items?.type != 'preview'" class="correct_answer">
                  <p>Correct</p>
                  <p style="padding-left: 10px; margin-bottom: 0;" v-html="content?.complete_answer"></p>
                </div>
              </div>
              <div v-if="question.content_main_picture && question.picture_bellow_text == 1" class="mt-3" style="text-align: center;">
                <img style="max-width: 600px; max-height: 300px;" :src="question.content_main_picture" alt="question_img">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-if="items?.type != 'preview'" class="d-flex align-items-center justify-content-center box_action_footer" style="padding: 20px 0;">
    <button v-if="!items?.isResult" style="min-width: 100px" type="button" class="btn btn-success" onclick="confirmSubmit()">Submit</button>
    <div v-if="items.topic_test_type == 'IELTS_READING' || items.topic_test_type == 'IELTS_LISTENING'" class="ml-auto pr-3" style="position: fixed; bottom: 20px; right: 16px; z-index: 99999; width: auto">
      <button style="min-width: 100px; color: white; font-weight: bold; display: none;" type="button" :class="'btn btn-warning s-btn-pre-section'" v-on:click.prevent="preSection(0)">Previous</button>
      <button style="min-width: 100px; margin-left: 20px; color: white; font-weight: bold;" type="button" :class="'btn btn-warning s-btn-next-section'" v-on:click.prevent="nextSection(0)">Next</button>
    </div>
  </div>

  <!-- Modal confirm submit -->
  <div class="modal fade modal-confirm-submit" data-keyboard="false" data-backdrop="static" style="position: fixed !important; top: 100px !important;" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content p-5">
        <h3 style="text-align: center;">Bạn có chắc chắn đã hoàn thành bài thi không?</h3>
        <div class="mt-5" style="text-align: center;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success ml-5" data-dismiss="modal" v-on:click.prevent="submit">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal time out -->
  <div class="modal fade modal-time-out" data-keyboard="false" data-backdrop="static" style="position: fixed !important; top: 100px !important;" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content p-5">
        <h3 style="text-align: center;">
          Đã hết giờ làm bài, Chúc bạn đạt kết quả cao. <br />
          Hãy nhấn nút Submit để xem kết quả
        </h3>
        <div class="mt-5" style="text-align: center;">
          <button type="button" class="btn btn-success" data-dismiss="modal" v-on:click.prevent="submit">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal test results -->
  <div class="modal fade modal-test-results" data-keyboard="false" data-backdrop="static" style="position: fixed !important; top: 100px !important; z-index: 99999;" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content p-5">
        <h3 style="text-align: center;" v-html="testType == 'IELTS_WRITING' ? 'Chúc mừng bạn đã hoàn thành bài test<br />Giáo viên sẽ chấm điểm và gửi kết quả cho bạn trong thời gian sớm nhất' : 'Điểm số của bạn'"></h3>
        <div class="row">
          <div class="col-6 offset-3">
            <div v-if="!testType || testType == 2" class="d-flex mt-5">
              <div v-if="scoreScaleReceived">
                <span v-if="scoreScaleReceived.vocabulary !== null">Vocabulary <br /></span>
                <span v-if="scoreScaleReceived.reading !== null">Reading <br /></span>
                <span v-if="scoreScaleReceived.writing !== null">Writing <br /></span>
                <span v-if="scoreScaleReceived.grammar !== null">Grammar <br /></span>
                <!-- <span v-if="scoreScaleReceived.listening !== null">Listening</span> -->
              </div>
              <div v-if="scoreScaleReceived" style="margin-left: auto; text-align: right;">
                <span v-if="scoreScaleReceived.vocabulary !== null"><span v-html="scoreScaleReceived.vocabulary"></span> <br /></span>
                <span v-if="scoreScaleReceived.reading !== null"><span v-html="scoreScaleReceived.reading"></span> <br /></span>
                <span v-if="scoreScaleReceived.writing !== null"><span v-html="scoreScaleReceived.writing"></span> <br /></span>
                <span v-if="scoreScaleReceived.grammar !== null"><span v-html="scoreScaleReceived.grammar"></span> <br /></span>
                <!-- <span v-if="scoreScaleReceived.listening !== null"><span v-html="scoreScaleReceived.listening"></span></span> -->
              </div>
            </div>
            <div v-if="(testType == 'IELTS_GRAMMAR' || resultType == 2) && resultTestIelts" class="d-flex justify-content-center mt-4 box-score_ielts_grammar">
              <span v-html="resultTestIelts.total_correct_answers"></span>/
              <span v-html="resultTestIelts.total_questions"></span>
              &nbsp;(<span v-html="resultTestIelts.percent_correct_answers"></span>%)
            </div>
            <div v-if="(testType == 'IELTS_READING' || testType == 'IELTS_LISTENING') && resultTestIelts" class="text-center mt-4 box-score_ielts_grammar">
              <span v-html="resultTestIelts.total_correct_answers"></span>/<span v-html="resultTestIelts.total_questions"></span> <br />
              <span v-html="resultTestIelts.score"></span>
            </div>
          </div>
        </div>
        <div class="mt-5" style="text-align: center;">
          <button type="button" class="btn btn-success" v-on:click.prevent="showDetail" v-html="testType == 'IELTS_WRITING' ? 'Xem lại bài' : 'Xem chi tiết'"></button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('script')
<script>
  var trial_test_token = `<?php echo $trial_test_token ?>`;
  if (trial_test_token) {
    localStorage.setItem("trial_test_token", trial_test_token);
  }
</script>

<script type="text/javascript">
  function confirmSubmit() {
    $('.modal-confirm-submit').modal('show');
  }

  function submit() {
    $('.modal-test-results').modal('show');
  }
</script>

<!-- Drag -->
<script type="text/javascript">
  function dragStart(event) {
    console.log(">>> dragStart <<<");
    event.dataTransfer.setData("draggedImageId", event.target.id);
    let parentNodeId = event.target.parentNode.getAttribute('drag-parent-id');
    if (!event.target.parentNode.hasAttribute('drag-parent-id')) {
      parentNodeId = event.target.parentNode.parentNode.parentNode.parentNode.getAttribute('drag-parent-id');
    }
    console.log(">>> dragStart, parentNodeId: ", parentNodeId);

    if (parentNodeId != 'container-answer-sort') {
      setTimeout(() => event.target.classList.toggle("hidden"));
    }
  }

  function dragEnd(event) {
    console.log(">>> dragEnd <<<");
    let parentNodeId = event.target.parentNode.getAttribute('drag-parent-id');
    if (!event.target.parentNode.hasAttribute('drag-parent-id')) {
      parentNodeId = event.target.parentNode.parentNode.parentNode.parentNode.getAttribute('drag-parent-id');
    }
    console.log(">>> dragEnd, parentNodeId: ", parentNodeId);

    if (parentNodeId != 'container-answer-sort') {
      event.target.classList.toggle("hidden");
    }
  }

  function dragOver(event) {
    console.log(">>> dragOver <<<");
    event.preventDefault();
  }

  function drop(event) {
    console.log(">>> drop <<<");
    const draggedImageId = event.dataTransfer.getData("draggedImageId");
    console.log(">>> event.dataTransfer: ", event.dataTransfer);
    const fromContent = document.getElementById(draggedImageId);
    const toContent = event.currentTarget;
    const toContainer = toContent.parentNode.parentNode.parentNode.parentNode;
    const fromContainer = fromContent.parentNode;
    let idParentToContainer = toContainer.getAttribute('drag-parent-id');
    let idParentFromContainer = fromContainer.getAttribute('drag-parent-id');

    if (!toContainer.hasAttribute('drag-parent-id')) {
      idParentToContainer = toContent.parentNode.getAttribute('drag-parent-id');
    }
    if (!fromContainer.hasAttribute('drag-parent-id')) {
      idParentFromContainer = fromContent.parentNode.parentNode.parentNode.parentNode.getAttribute('drag-parent-id');
    }

    console.log(">>> idParentToContainer: ", idParentToContainer);
    console.log(">>> idParentFromContainer: ", idParentFromContainer);

    fromContent.classList.remove("style_template");
    fromContent.classList.remove("style_answer");
    fromContent.classList.remove("set_width_150");
    // fromContent.classList.remove("question-3-style_answer");
    toContent.classList.remove("style_template");
    toContent.classList.remove("style_answer");
    toContent.classList.remove("set_width_150");
    // toContent.classList.remove("question-3-style_answer");

    const cloneHtmlFromContent = fromContent.innerHTML;
    const cloneHtmlToContent = toContent.innerHTML;
    console.log(">>> fromContent: ", fromContent);
    console.log(">>> toContent: ", toContent);
    console.log(">>> cloneHtmlFromContent: ", cloneHtmlFromContent);
    console.log(">>> cloneHtmlToContent: ", cloneHtmlToContent);
    fromContent.innerHTML = cloneHtmlToContent;
    toContent.innerHTML = cloneHtmlFromContent;
    if (idParentToContainer != idParentFromContainer && (!cloneHtmlFromContent.trim() || !cloneHtmlToContent.trim())) {
      if (idParentFromContainer == 'container-answer-sort') {
        toContent.hidden = true;
        fromContent.classList.add("style_template");
        if (idParentFromContainer == 'container-answer-sort') {
          fromContent.classList.add("set_width_150");
          // if (idParentFromContainer == 'question-3-answer') {
          //   fromContent.classList.add("set_style_question_3");
          // }
        }
      } else {
        fromContent.hidden = true;
        toContent.classList.add("style_template");
        if (idParentToContainer == 'container-answer-sort') {
          toContent.classList.add("set_width_150");
          // if (idParentToContainer == 'question-3-answer') {
          //   toContent.classList.add("set_style_question_3");
          // }
        }
      }
    } else {
      if (fromContent.innerHTML.trim()) {
        fromContent.classList.add("style_template");
        if (idParentFromContainer == 'container-answer-sort') {
          fromContent.classList.add("set_width_150");
          // if (idParentFromContainer == 'question-3-answer') {
          //   fromContent.classList.add("set_style_question_3");
          // }
        }
      } else {
        if (idParentFromContainer == 'container-template-sort') {
          // fromContent.classList.add("question-3-style_answer");
        } else {
          fromContent.classList.add("style_answer");
        }
      }

      if (toContent.innerHTML.trim()) {
        toContent.classList.add("style_template");
        if (idParentToContainer == 'container-answer-sort') {
          toContent.classList.add("set_width_150");
          // if (idParentToContainer == 'question-3-answer') {
          //   toContent.classList.add("set_style_question_3");
          // }
        }
      } else {
        if (idParentToContainer == 'container-template-sort') {
          // toContent.classList.add("question-3-style_answer");
        } else {
          toContent.classList.add("style_answer");
        }
      }
    }

    trial_test.reloadMatchingAfterSort();
  }
</script>
<script src="/js/user/trial_test/trial_test.js?v={{config('common.version')}}"></script>
@endpush
