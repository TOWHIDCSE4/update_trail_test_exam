@extends('layout.admin.main')
@section('content')
<link rel="stylesheet" href="/css/admin/create_question.css?v={{config('common.version')}}">
<div id="page_create_question_trial_test" class='container-trial-test' style="display: none">
    <div class="title-trial-test" v-if="!question_id">Create New Question</div>
    <div class="title-trial-test" v-if="question_id">Edit Question</div>
    <hr style="border-top: 1px solid #51595C">
    <div style="width: 90%;margin: 0 10px" id="question_area">
        <div class="group-question">
            <div class="label-question">Title<span style="color: #ff4d4f"> *</span></div>
            <div class="group-question mb-0">
                <div style="display: flex; align-items: center">
                    <div v-if="!file_audio_title">Choose Audio</div>
                    <label v-if="!file_audio_title" for="audio_title" class="btn btn-primary label-select-audio">Select file</label>
                    <input id="audio_title" name="audio-title" ref="fileInput" class="c_h_file-input" @change="changeFile(event, 'audio_title')" type="file" accept="audio/*" style="display: none" />
                    <div style="display: flex; align-items: center">
                        <audio class="audio-title-view" v-if="file_audio_title" controls="controls">
                            <source v-if="file_audio_title" :src="file_audio_title">
                        </audio>
                        <i v-if="file_audio_title" class="fas fa-trash-alt trash-main-question hover-icon-delete" @click="deleteFile('audio_title')" style="margin-left: 5px"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="group-question">
            <div class="label-question"></div>
            <div style="width: 100%">
                <input type="text" class="form-control" id="title_question" name="title-question" v-model="title_question">
                <div class="error-question" id="title_error"></div>
            </div>
            <div class="check-voice">
                <input type="checkbox" class="input-checkbox-question" v-model="voice_title">To voice
            </div>
        </div>
        <div class="group-question">
            <div class="label-question">Type<span style="color: #ff4d4f"> *</span></div>
            <select v-if="!question_id && category_question !== value_category.IELTS_Writing" class="form-control input-short" id="type_question" name="type-question" v-model="type_question">
                <option v-for="item in array_type_question" :value="item.value">@{{ item.title }}</option>
            </select>
            <input class="form-control input-short" v-for="item in array_type_question" v-if="(question_id || category_question == value_category.IELTS_Writing) && item.value === type_question" :value="item.title" disabled>
        </div>
        <div class="group-question">
            <div class="label-question">Category<span style="color: #ff4d4f"> *</span></div>
            <select v-if="!question_id" class="form-control input-short" id="category_question" name="category-question" v-model="category_question">
                <option v-for="item in array_category_question" :value="item.value">@{{ item.title }}</option>
            </select>
            <input class="form-control input-short" v-for="item in array_category_question" v-if="question_id && item.value === category_question" :value="item.title" disabled>
        </div>
        <div class="group-question">
            <div class="label-question">Point<span style="color: #ff4d4f"> *</span></div>
            <input type="number" class="form-control input-short" id="point_question" name="point-question" :disabled="category_question == value_category.IELTS_Writing" v-model="point_question" min="0" @change="resetPoint()">
        </div>
        <div v-if="type_question !== title_type_question.matching && category_question !== value_category.IELTS_Writing">
            <div class="group-question">
                <div class="label-question">Content<span style="color: #ff4d4f" v-if="type_question !== title_type_question.choose"> *</span></div>
                <div class="group-question" id="area_file_content">
                    <div style="display: flex; align-items: center">
                        <div v-if="!file_audio_question">Choose Audio</div>
                        <label v-if="!file_audio_question" for="audio_question" class="btn btn-primary label-select-audio">Select file</label>
                        <input id="audio_question" name="audio-question" ref="fileInput" class="c_h_file-input" @change="changeFile(event, 'audio_question')" type="file" accept="audio/*" style="display: none" />
                        <div style="display: flex; align-items: center">
                            <audio class="audio-question-view" v-if="file_audio_question" controls="controls">
                                <source v-if="file_audio_question" :src="file_audio_question">
                            </audio>
                            <i v-if="file_audio_question" class="fas fa-trash-alt trash-main-question hover-icon-delete" @click="deleteFile('audio_question')" style="margin-left: 5px"></i>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center">
                        <div v-if="!file_image_question" style="margin-bottom: 5px">Choose Picture</div>
                        <label v-if="!file_image_question" for="image_question" class="btn btn-primary" style="margin-left: 5px; margin-bottom: 0">Select file</label>
                        <input id="image_question" @change="changeFile(event,'image_question')" name="image-question" ref="fileInput" class="c_h_file-input" type="file" accept="image/*" style="display: none" />
                        <div style="display: flex; align-items: center">
                            <img v-if="file_image_question" class="img-question-view" :src="file_image_question ? file_image_question : ''">
                            <i v-if="file_image_question" class="fas fa-trash-alt trash-main-question hover-icon-delete" @click="deleteFile('image_question')" style="margin-left: 5px"></i>
                        </div>
                    </div>
                    <div class="group-question">
                        <input type="checkbox" id="layout_picture_checkbox" v-model="layout_picture_check" name="layout-picture-checkbox" class="input-checkbox-question">
                        <div style="width: 140px">Picture below text</div>
                    </div>
                </div>
            </div>
            <div class="group-question">
                <div class="label-question"></div>
                <div style="width: 100%">
                    <div>
                        <textarea id="content_question_main" rows="5" name="content-question-main" v-model="content_question_main" class="form-control text-area-disable-resize"></textarea>
                    </div>
                    <div class="error-question" id="content_question_main_error"></div>
                    <div v-if="type_question === title_type_question.sort">for example: ??|??|??|??|people|??|??|??</div>
                    <div v-if="type_question === title_type_question.typing || type_question === title_type_question.dropdown">Separate by “|” For example He|??.</div>
                    <div v-if="type_question === title_type_question.choose" v-text="'If you need to display the underline, add <u> before the text and </u> after the text. Example: <u>Hello</u>'"></div>
                </div>
                <div class="check-voice" style="margin-bottom: 120px" v-if="type_question !== title_type_question.typing">
                    <input type="checkbox" class="input-checkbox-question" v-model="voice_content_main">To voice
                </div>
            </div>
        </div>
        <div id="area_arrange_question" v-if="type_question === title_type_question.sort && category_question !== value_category.IELTS_Writing">
            <div>Correct Answer <span style="color: #ff4d4f"> *</span></div>
            <div class="group-question">
                <div class="label-question"></div>
                <div class="group-input-question">
                    <input type="text" class="form-control" id="correct_answer_sort" name="correct-answer-sort" v-model="correct_answer_sort">
                    <div class="error-question" id="correct_answer_sort_error"></div>
                    <div>Separate by “|” For example apple|orange|banana</div>
                </div>
                <div class="check-voice" style="margin-bottom: 25px">
                    <input type="checkbox" class="input-checkbox-question" v-model="voice_answer">To voice
                </div>
            </div>
            <div>Incorrect Answer</div>
            <div class="group-question">
                <div class="label-question"></div>
                <div class="group-input-question">
                    <input type="text" class="form-control" id="incorrect_answer_sort" v-model="incorrect_answer_sort" name="incorrect-answer-sort">
                    <div>Separate by “|” For example apple|orange|banana</div>
                </div>
            </div>
        </div>
        <div id="area_match_question" v-if="type_question === title_type_question.matching && category_question !== value_category.IELTS_Writing">
            <div class="group-question" style="align-items: unset">
                <div class="label-question">Content<span style="color: #ff4d4f"> *</span></div>
                <div style="width: 100%">
                    <div style="align-items: center;margin-top:20px" v-for="(questionMatch, indexMatch) in list_question_matching">
                        @include('admin.trial_test.component.tab_question')
                    </div>
                </div>
            </div>
        </div>
        <div id="area_choose_question" v-if="type_question === title_type_question.choose && category_question !== value_category.IELTS_Writing">
            <div class="group-question">
                <div class="label-question"></div>
                <div style="width: 100%">
                    <div>
                        <div style="margin: 10px 0">
                            <button class="btn btn-primary" @click="addComponent('qna')" v-if="list_qna_question && list_qna_question.length < 10">Add question</button>
                        </div>
                        <div id="sort_qna_question">
                            <div v-for="(qna, indexQna) in list_qna_question" :key="indexQna" :data-id="indexQna" style="display: flex;align-items: center">
                                <div class="component_question">
                                    <i class="fas fa-trash-alt trash-question icon-action hover-icon-delete" v-if="list_qna_question.length > 1" @click="removeComponent('qna', indexQna, qna.order)"></i>
                                    <div :data-id="indexQna" :data-name="qna.content">Question @{{ indexQna + 1 }}<span style="color: #ff4d4f"> *</span></div>
                                    <div class="group-question">
                                        <div class="label-question"></div>
                                        <div style="width: 85%">
                                            <input type="text" class="form-control input-question-millionaire" v-model="qna.content">
                                            <div class="error-question" :id="'question_qna_error'+ indexQna"></div>
                                        </div>
                                        <div class="check-voice">
                                            <input type="checkbox" class="input-checkbox-question" v-model="qna.voice_content">To voice
                                        </div>
                                    </div>

                                    <div>Correct Answer<span style="color: #ff4d4f"> *</span></div>
                                    <div class="group-question" v-for="(item, indexCorrect) in qna.correct">
                                        <div class="label-question"></div>
                                        <div style="width: 75%">
                                            <input type="text" class="form-control input-question-millionaire" v-model="item.subCorrect">
                                            <div v-if="indexCorrect == 0" class="error-question" :id="'correct_answer_qna_error'+indexQna+'_0'"></div>
                                        </div>
                                        <div class="check-voice" v-if="qna.correct.length == 1">
                                            <input type="checkbox" class="input-checkbox-question" v-model="qna.voice_answer">To voice
                                        </div>
                                        <div v-if="qna.correct.length > 1">
                                            <i class="fas fa-minus-circle icon-action hover-icon-delete" style="font-size: 25px;margin-left: 0;" @click="removeComponent('qna-correct-answer', indexQna, null, indexCorrect)"></i>
                                        </div>
                                    </div>
                                    <div class="group-question">
                                        <div class="label-question"></div>
                                        <i class="fas fa-plus-circle icon-action hover-icon-add" style="font-size: 25px;margin-left: 0;" @click="addComponent('qna-correct-answer', indexQna)"></i>
                                    </div>

                                    <div>Incorrect Answer<span style="color: #ff4d4f"> *</span></div>
                                    <div class="group-question" v-for="(item, indexIc) in qna.incorrect">
                                        <div class="label-question"></div>
                                        <div style="width: 75%">
                                            <input type="text" class="form-control input-question-millionaire" v-model="item.ic">
                                            <div v-if="indexIc == 0" class="error-question" :id="'incorrect_answer_qna_error'+indexQna+'_0'"></div>
                                        </div>
                                        <div v-if="qna.incorrect.length > 1">
                                            <i class="fas fa-minus-circle icon-action hover-icon-delete" style="font-size: 25px;margin-left: 5px;" @click="removeComponent('qna-incorrect-answer', indexQna, null, indexIc)"></i>
                                        </div>
                                    </div>
                                    <div class="group-question">
                                        <div class="label-question"></div>
                                        <i class="fas fa-plus-circle icon-action hover-icon-add" style="font-size: 25px;margin-left: 0;" @click="addComponent('qna-incorrect-answer', indexQna)"></i>
                                    </div>
                                </div>
                                <div class="sort-qna-question-item" v-if="list_qna_question.length > 1" :data-id="indexQna">
                                    <i class="fas fa-grip-vertical icon-action hover-icon-action" ></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="area_typing_question" v-if="type_question === title_type_question.typing && category_question !== value_category.IELTS_Writing">
            <div style="margin: 10px 0; display: flex;align-items: center">
                <div>Correct Answer</div>
                <button style="margin-left: 20px" class="btn btn-primary" @click="addComponent('typing')">Add answer
                </button>
            </div>
            <div id="sort_typing_question">
                <div class="group-question" v-for="(typingAnswer, indexTyAns) in list_typing_correct_answer" :data-id="indexTyAns" style="align-items: flex-start">
                    <div class="label-question">Answer @{{ indexTyAns + 1 }}<span style="color: #ff4d4f"> *</span></div>
                    <div class="group-input-question">
                        <textarea type="text" rows="3" class="form-control" v-model="typingAnswer.content"></textarea>
                        <div class="error-question" :id="'answer_typing_error'+ indexTyAns"></div>
                        <div>Separate by “|” For example apple|orange|banana</div>
                    </div>
                    <div style="text-align: center">
                        <i style="margin-bottom: 5px" v-if="list_typing_correct_answer.length > 1" class="fas fa-trash-alt trash-typing-question hover-icon-delete" @click="removeComponent('typing', indexTyAns, typingAnswer.order)"></i>
                        <div class="sort-typing-question-item" v-if="list_typing_correct_answer.length > 1" :data-id="indexTyAns">
                            <i class="fas fa-grip-vertical icon-action hover-icon-action"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="area_dropdown_question" v-if="type_question === title_type_question.dropdown && category_question !== value_category.IELTS_Writing">
            <div style="display: flex">
                <div>Dropdown<span style="color: #ff4d4f"> *</span></div>
                <div class="group-question" style="margin-left: 30px"><input type="checkbox" class="input-checkbox-question" v-model="flag_show_dropdown">Show dropdown</div>
            </div>
            <div class="group-question">
                <div class="label-question"></div>
                <div style="width: 100%">
                    <div style="display: flex;align-items: center">
                                <div class="component_question">
                                    <div class="group-question">
                                        <div style="width: 30px ;margin: 0 15px; text-align: center"></div>
                                        <div style="width: 30%; margin-right: 10px">
                                            Option<span style="color: #ff4d4f"> *</span>
                                        </div>
                                        <div style="width: 60%">
                                            Description
                                        </div>
                                    </div>
                                    <div v-for="(dropdown, indexDropdown) in list_dropdown" :key="indexDropdown" :data-id="indexDropdown">
                                        <div class="group-question">
                                            <div style="width: 30px ;margin: 0 15px; text-align: center">@{{ dropdown.value }}</div>
                                            <div style="width: 30%; margin-right: 10px">
                                                <input type="text" class="form-control input-question-millionaire" v-model="dropdown.content">
                                            </div>
                                            <div style="width: 60%">
                                                <input type="text" class="form-control input-question-millionaire" v-model="dropdown.description">
                                            </div>
                                            <div v-if="list_dropdown.length > 1">
                                                <i class="fas fa-minus-circle icon-action hover-icon-delete" style="font-size: 25px;margin-left: 5px;" @click="removeComponent('dropdown', indexDropdown)"></i>
                                            </div>
                                        </div>
                                        <div class="group-question">
                                            <div style="width: 30px ;margin: 0 15px; text-align: center"></div>
                                            <div style="width: 30%; margin-right: 10px">
                                                <div class="error-question" :id="'question_dropdown_error'+ indexDropdown"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="group-question">
                                        <div style="width: 60px"></div>
                                        <i class="fas fa-plus-circle icon-action hover-icon-add" style="font-size: 25px;margin-left: 0;" @click="addComponent('dropdown',list_dropdown.length)"></i>
                                    </div>
                                </div>
                            </div>
                </div>
            </div>
            <div>Correct answer<span style="color: #ff4d4f"> *</span></div>
            <div class="group-question">
                <div class="label-question"></div>
                <div style="width: 100%">
                    <input type="text" class="form-control" v-model="correct_answer_dropdown">
                    <div class="error-question" :id="'correct_answer_dropdown_error'"></div>
                    <div>Separate by “|” For example 2|1|3</div>
                </div>
            </div>
        </div>
        <div id="area_ielts_writing" v-show="category_question == value_category.IELTS_Writing">
            <div class="label-question">Content<span style="color: #ff4d4f" v-if="type_question !== title_type_question.choose"> *</span></div>
            <div class="group-question">
                <div class="label-question"></div>
                <div style="width: 100%">
                    <div>
                        <textarea id="content_ielts_writing" rows="5" name="content-ielts-writing"></textarea>
                    </div>
                    <div class="error-question" id="content_ielts_writing_error"></div>
                </div>
            </div>
            <div class="label-question" style="width: 200px">Minimum words</div>
            <div class="group-question">
                <div class="label-question"></div>
                <input type="number" class="form-control input-short" id="word_minimum" name="word-minimum" v-model="word_minimum" min="0" @change="resetPoint()">
            </div>
        </div>
        <div class="group-question">
            <div class="label-question"></div>
            <div>
                <button class="btn btn-dark" v-on:click="window.location.href='/admin/trial-test/list-questions/{{$topic_id}}'">Back
                </button>
                <button class="btn btn-success" v-on:click="saveQuestion()" style="margin-left: 20px;width: 150px">
                    <span v-if="!question_id" style="color: white">Create new</span>
                    <span v-else style="color: white">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')
<script>
    var question_id = '{{ !empty($question_id) ? $question_id : "" }}';
    var topic_id = '{{ !empty($topic_id) ? $topic_id : "" }}';
    var section_id = '{{ !empty($section_id) ? $section_id : "" }}';
</script>
<script src="/js/admin/trial_test/create_question.js?v={{config('common.version')}}"></script>
<script>
    tinymce.init({
        selector: '#content_ielts_writing',
        height: 350,
        menubar: true,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: `undo redo | formatselect | fontsizeselect | forecolor | bold italic underline | backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent |
                    link image media | removeformat`,
        images_upload_handler: callApiUpImageTextEditor,
        setup: function(editor) {
            editor.on('keyup', function (e) {
                $('#content_ielts_writing').val(editor.getContent());
            });
            editor.on('change', function (e) {
                $('#content_ielts_writing').val(editor.getContent());
            });
        },
        paste_data_images: true
    });
</script>
@endpush
