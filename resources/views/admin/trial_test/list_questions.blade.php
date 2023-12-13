@extends('layout.admin.main')
@section('content')
    <div id="list_question_trial_test" class='container-trial-test'>
        <div class="row">
            <div class="title-trial-test col-sm-6">Sections @if(!empty($name_topic))<span>- Topic: {{ $name_topic }}</span> @endif </div>
            <div class="col-sm-6" style="text-align: end;margin-top: 10px">
                <button class="btn btn-ispeak btn-dark" onclick="window.location.href='/admin/trial-test'" style="margin-right: 60px">Back</button>
                <button class="btn btn-ispeak btn-primary" @click="previewTest">PreTest</button>
            </div>
        </div>
        <div id='tab_question_area' style="display: none">
        <div class="tab" id="tab_question">
            <button v-for="(item,indexSection) in sections" class="tablinks" :class="indexSection === tab_active ? 'active' : ''" v-on:click="openSection(item.id, indexSection)">@{{ indexSection+1 }}</button>
            <button class="tablinks" id="button_add_section" v-on:click="openModalCreateSection" v-if="sections.length < 8"><i class="fa fa-plus" style="color: white;background-color: #08BF5A; font-size: 25px"></i></button>
        </div>
        <div  v-for="(item, indexSection) in sections" :id="item.id" :class="'tabcontent tab-content-'+item.id" style="padding: 15px 0" :style="indexSection === tab_active ? 'display:block': 'display:none'">
            <div class="row" style="margin: 10px 0">
                <div class="title-trial-test col-sm-6 " style="font-size: 20px;padding-left: 0; margin: 0" v-if="item.section_name"><div class="text-truncate" style="max-width: 300px">Sections: @{{ item.section_name }}</div></div>
                <div class="col-sm-6" style="text-align: right">
                    <button class="btn" @click="viewPassage" style="margin-left: 15px;background-color: #08BF5A;color: white ;margin-right: 35px" v-if="item.passage || item.audio_main"><i class="fa fa-eye" style="background-color: #08BF5A; color: white"></i> View </button>
                    <button class="btn btn-light" data-toggle="modal" data-target="#modal_topic_trial_test" @click="openModalEditSection(item.id)" style="margin: 5px 25px 5px 5px">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-light" data-toggle="modal" data-target="#modal_topic_trial_test" @click="deleteSection(item.id)" style="margin: 5px">
                        <i class="fas fa-trash" style="color: #F44336"></i>
                    </button>
                </div>
            </div>
            <div class="title-trial-test col-sm-6" style="font-size: 20px;padding-left: 0">List Question</div>
            @include('admin.trial_test.component.table_question')
        </div>
        </div>
        <div id="modal_section" class="modal fade" data-keyboard="false"
             data-backdrop="static"
             tabindex="-1" role="dialog" style="display:none">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div id="" class="modal-header">
                        <i class="fa fa-times hover-icon-action close-modal-create" @click="closeModalSection()"></i>
                        <h2 class="text-center" v-if="action_type == 1">Create New Section</h2>
                        <h2 class="text-center" v-if="action_type == 2">Edit Section</h2>
                    </div>
                    <div id="modal_body_trial-test" class="modal-body modal-body-main">
                        <div class="inner modal-box-body">
                            <div class="modal-input" style="margin-left: 10px">
                                <span class="label-modal-section">Title</span>
                                <div style="width: 90%;display: grid">
                                    <input class="form-control" type="text" id="input_section_name" @change="changeNameSection()" name="input-section-name" v-model="section_name">
                                    <div class="error-input" id="input_section_name_error"></div>
                                </div>
                            </div>
                            <div class="modal-input" style="margin-left: 10px">
                                <span class="label-modal-section">Audio</span>
                                <div style="width: 60%;display: grid">
                                    <label v-if="!audio_main" for="audio_main" class="btn btn-primary label-select-audio">Select file</label>
                                    <input id="audio_main" name="audio-main" ref="fileInput" class="c_h_file-input" @change="changeFile(event)" type="file" accept="audio/*" style="display: none" />
                                    <div style="display: flex; align-items: center">
                                        <audio v-if="audio_main" class="audio-main-view" id="main_audio_section" controls="controls" preload>
                                            <source v-if="audio_main" :src="audio_main">
                                        </audio>
                                        <i v-if="audio_main" class="fas fa-trash-alt trash-main-question hover-icon-delete" @click="deleteFile()" style="margin-left: 5px"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-input" style="margin-left: 10px">
                                <span class="label-modal-section">Passage</span>
                                <div style="width: 90%;display: grid">
                                    <textarea id="input_passage"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="" class="modal-footer" style="margin-bottom: 20px;margin-top: -10px">
                        <button class="btn btn-ispeak btn-success" type="button" style="width: 100px;margin-right: 0" @click="saveSection()">
                            <span v-if="action_type == 1" style="color: white">Create</span>
                            <span v-if="action_type == 2" style="color: white">Update</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="modal_view_passage" class="modal fade" data-keyboard="false"
             data-backdrop="static"
             tabindex="-1" role="dialog" style="display:none">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div id="" class="modal-header">
                        <i class="fa fa-times hover-icon-action close-modal-create" @click="closeModalViewSection()"></i>
                        <h2 class="text-center">View</h2>
                    </div>
                    <div id="modal_body_trial-test" class="modal-body modal-body-main">
                        <div class="inner modal-box-body">
                            <div class="modal-input" style="margin-left: 10px" v-if="audio_main">
                                <div class="label-modal-section">Audio</div>
                                <audio v-if="audio_main" id="main_audio_view" class="audio-main-view" controls="controls" preload  style="display: none">
                                    <source v-if="audio_main" :src="audio_main">
                                </audio>
                            </div>
                            <div class="modal-input" style="margin-left: 10px" v-if="passage">
                                <div class="label-modal-section">Passage</div>
                                <div class="area-text-editor" style="width: 100%;display: grid;margin-top: 16px">
                                    <div v-html="passage"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        var topic_id = '{{ !empty($topic_id) ? $topic_id : "" }}';
    </script>
    <script src="/js/admin/trial_test/list_question.js?v={{config('common.version')}}"></script>
    <script>
        tinymce.init({
            selector: '#input_passage',
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
                    $('#input_passage').val(editor.getContent());
                });
                editor.on('change', function (e) {
                    $('#input_passage').val(editor.getContent());
                });
            },
            paste_data_images: true
        });
    </script>
@endpush
