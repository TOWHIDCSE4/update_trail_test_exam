// Constant
const TEST_TYPE = {
    NORMAL: 1,
    STAFF_PRE_TEST: 2
};

const app_list_section_tt = new Vue({
    el: "#list_question_trial_test",
    data: {
        action_type: 1,
        id_section_edit: null,
        section_name: '',
        passage: '',
        audio_main: '',
        sections: {
            id: '',
            section_name: '',
            audio_main: '',
            passage: '',
            questions: [],
            listCheckQuestion: [],
            check_all: false,
            array_sort: [],
            ids_sort: [],
        },
        tab_active: 0,
        topic_id: topic_id,
    },
    components: {},
    mounted() {
        $('#tab_question').hide();
        $('#tab_question_area').hide();
        this.initData();
    },
    created() {
    },
    computed: {},
    methods: {
        initData: function (type = "basic") {
            var self = this;
            axios.defaults.baseURL = baseUrl;
            axios.defaults.headers.common.authorization =
                localStorage.getItem("trial_test_token");
            $("body").LoadingOverlay("show");
            if (type === "sort") {
                var urlInit = "/admin/trial-test/data-questions-with-sort";
            } else {
                var urlInit = "/admin/trial-test/data-questions";
            }
            axios
                .post(urlInit, {
                    topic_id: self.topic_id,
                    section_id: self.sections.length > 0 ? self.sections[self.tab_active].id : null,
                    array_sort: self.sections.length > 0 ? self.sections[self.tab_active].array_sort : [],
                    ids_sort: self.sections.length > 0 ? self.sections[self.tab_active].ids_sort : [],
                    type: type,
                })
                .then((response) => {
                    if (response.data.code === "10000") {
                        self.sections = [];
                        Vue.nextTick(function () {
                            var data = response.data.data;
                            data.forEach(function (item, index) {
                                self.sections.push(
                                    {
                                        id: item.id,
                                        section_name: item.section_name,
                                        passage: item.passage,
                                        audio_main: item.audio ?? '',
                                        questions: item.questions,
                                        listCheckQuestion: [],
                                        check_all: false,
                                        array_sort: [],
                                        ids_sort: [],
                                    }
                                )
                            })
                            self.check_all = false;
                            $(".check_once_question").each(function () {
                                $(this).prop("checked", false);
                            });
                        });
                    }
                    if (response.data.code === "10002") {
                        alert(response.data.message);
                        window.location.href = "/admin/trial-test";
                    }
                    $('#tab_question').show();
                    $('#tab_question_area').show();
                    $("body").LoadingOverlay("hide");
                    setTimeout(function () {
                        $("#sort_table_question"+ self.tab_active).sortable({
                            items: "tr",
                            cursor: "pointer",
                            axis: "y",
                            dropOnEmpty: true,
                            start: function (e, ui) {
                                ui.item.addClass("selected");
                            },
                            stop: function (e, ui) {
                                ui.item.removeClass("selected");
                            },
                            update: function (c, d) {
                                self.sections[self.tab_active].array_sort = [];
                                self.sections[self.tab_active].array_sort = $(this).sortable("toArray", {
                                    attribute: "data-index",
                                });
                                self.sections[self.tab_active].ids_sort = [];
                                self.sections[self.tab_active].ids_sort = $(this).sortable("toArray", {
                                    attribute: "data-id",
                                });
                                self.initData("sort");
                            },
                        });
                    }, 500);
                })
                .catch((errors) => {
                    $('#tab_question').show();
                    $('#tab_question_area').show();
                    $("body").LoadingOverlay("hide");
                    if (errors?.response?.data?.message) {
                        alert(errors?.response?.data?.message);
                        if (type === "sort") {
                            self.initData();
                        }
                    } else {
                        alert("Error");
                    }
                });
            // var url = '/admin/trial-test/data-topic';
            // var params = {};
            // const res = await callAxiosGet(url, params);
            // if(await res){
            //     self.topics = res.data;
            //     console.log(self.topics);
            //     console.log(1111);
            // }else{
            //     alert('error')
            // }
        },
        formatTime: function (value) {
            return value ? moment(value).format("YYYY-MM-DD") : "";
        },
        openSection: function (section_id, index) {
            var self = this;
            self.tab_active = index;
        },
        checkAllQuestion: function () {
            var self = this;
            self.sections[self.tab_active].listCheckQuestion = [];
            if (!self.check_all) {
                $(".check_once_question" + self.tab_active).each(function () {
                    $(this).prop("checked", true);
                    self.sections[self.tab_active].listCheckQuestion.push($(this).data("id"));
                });
            } else {
                $(".check_once_question" + self.tab_active).each(function () {
                    $(this).prop("checked", false);
                });
            }
        },
        checkOneQuestion: function (id) {
            var self = this;
            var index = self.sections[self.tab_active].listCheckQuestion.indexOf(id);
            if ($("#check_question" + self.tab_active + '_' + id).prop("checked")) {
                self.sections[self.tab_active].listCheckQuestion.push(id);
                if (
                    self.sections[self.tab_active].listCheckQuestion.length ===
                    $(".check_once_question" + self.tab_active).length
                ) {
                    self.sections[self.tab_active].check_all = true;
                }
            } else {
                self.sections[self.tab_active].check_all = false;
                self.sections[self.tab_active].listCheckQuestion.splice(index, 1);
            }
        },
        deleteQuestion: function () {
            var self = this;
            if (self.sections[self.tab_active].listCheckQuestion && self.sections[self.tab_active].listCheckQuestion.length === 0) {
                alert("Please select at least one question");
                return;
            } else {
                var confirmDelete = confirm("Are you sure you want to delete?");
                if (confirmDelete) {
                    var self = this;
                    axios.defaults.baseURL = baseUrl;
                    axios.defaults.headers.common.authorization =
                        localStorage.getItem("trial_test_token");
                    $("body").LoadingOverlay("show");
                    axios
                        .post("/admin/trial-test/delete-questions", {
                            topic_id: self.topic_id,
                            section_id: self.sections[self.tab_active].id,
                            list_question: self.sections[self.tab_active].listCheckQuestion,
                        })
                        .then((response) => {
                            if (response.data.code === "10000") {
                                self.initData();
                                self.sections[self.tab_active].check_all = false;
                                $(".check_once_question" + self.tab_active).each(function () {
                                    $(this).prop("checked", false);
                                });
                            }
                            $("body").LoadingOverlay("hide");
                        })
                        .catch((errors) => {
                            $("body").LoadingOverlay("hide");
                            if (errors?.response?.data?.message) {
                                alert(errors?.response?.data?.message);
                            } else {
                                alert("Error");
                            }
                        });
                }
            }
        },
        previewTest: function () {
            // const windowReference = window.open();
            $("body").LoadingOverlay("show");
            axios
                .post("/create-session-test", {
                    test_type: TEST_TYPE.STAFF_PRE_TEST,
                    test_result_id: null,
                    test_topic_id: topic_id || null,
                    user_Oid: null,
                    url_callback: "",
                })
                .then((response) => {
                    if (response.data.code === "10000") {
                        var token = localStorage.getItem("trial_test_token");
                        const url =
                            "/student/trial-test?code=" +
                            response.data.data.code +
                            "&test_type=2&trial_test_token=" +
                            token;
                        // windowReference.location = url;
                        setTimeout(() => {
                            window.open(url, '_blank');
                        }, 500)
                    } else {
                        if (response?.data?.message) {
                            alert(response?.data?.message);
                        } else {
                            alert("Error");
                        }
                    }
                    $("body").LoadingOverlay("hide");
                })
                .catch((errors) => {
                    $("body").LoadingOverlay("hide");
                    if (errors?.response?.data?.message) {
                        alert(errors?.response?.data?.message);
                    } else {
                        alert("Error");
                    }
                });
        },
        changeNameSection: function () {
            if (this.section_name) {
                $('#input_section_name_error').text('');
            }
        },
        viewPassage: function () {
            this.passage = this.sections[this.tab_active].passage;
            this.audio_main = this.sections[this.tab_active].audio_main;
            $("body").LoadingOverlay("show");
            setTimeout(() => {
                var audio = document.getElementById("main_audio_view");
                if(audio) {
                    audio.load();
                }
            }, 500)
            setTimeout(() => {
                $('#main_audio_view').show();
                $("body").LoadingOverlay("hide");
            }, 600)
            $('#modal_view_passage').modal('show');
        },
        openModalCreateSection: function () {
            var self = this;
            self.action_type = 1;
            self.id_section_edit = null;
            self.section_name = '';
            self.audio_main = '';
            self.passage = '';
            $('#audio_main').val('');
            $('#input_passage').val('');
            $(tinymce.get('input_passage').getBody()).html('');
            $('#modal_section').modal('show');
        },
        openModalEditSection: function (sectionId) {
            var self = this;
            self.action_type = 2;
            $('#audio_main').val('');
            self.id_section_edit = sectionId;
            axios.defaults.baseURL = baseUrl;
            $('#input_passage').val('');
            const auth_token = localStorage.getItem('trial_test_token');
            axios.defaults.headers.common.authorization = auth_token;
            $('body').LoadingOverlay('show');
            axios.get('/admin/trial-test/data-section', {
                params: {
                    id_section: sectionId,
                },
            }).then((response) => {
                if (response.data.code === '10000') {
                    self.section_name = response.data.data.section_name;
                    self.audio_main = response.data.data.audio;
                    $('#input_passage').val(response.data.data.passage);
                    $(tinymce.get('input_passage').getBody()).html(response.data.data.passage);
                    setTimeout(() => {
                        var audio = document.getElementById("main_audio_section");
                        if(audio) {
                            audio.load();
                        }
                    }, 500)
                    setTimeout(() => {
                        $("body").LoadingOverlay("hide");
                    }, 600)
                    $('#modal_section').modal('show');
                }
                $('body').LoadingOverlay('hide');
            }).catch((errors) => {
                $('body').LoadingOverlay('hide');
                if (errors?.response?.data?.message) {
                    alert(errors?.response?.data?.message);
                } else {
                    alert("Error");
                }
            });
        },
        saveSection: function () {
            var self = this;
            var flagError = 0;
            if (!self.section_name) {
                $('#input_section_name_error').text('Title section is required')
                flagError = 1;
            }
            if (flagError == 1) {
                return;
            }
            axios.defaults.baseURL = baseUrl;
            var auth_token = localStorage.getItem('trial_test_token');
            axios.defaults.headers.common.authorization = auth_token;
            $('body').LoadingOverlay('show');
            axios.post('/admin/trial-test/save-section', {
                id_topic: self.topic_id,
                id_section: self.id_section_edit,
                section_name: self.section_name,
                audio: self.audio_main,
                passage: $('#input_passage').val()
            }).then((response) => {
                var audio = document.getElementById(
                    "main_audio_section"
                );
                if (response.data.code === '10000') {
                    self.initData();
                    if(audio) {
                        audio.pause();
                    }
                    $('#modal_section').modal('hide');
                } else if (response.data.code === '10002') {
                    if(audio) {
                        audio.pause();
                    }
                    $('#input_section_name_error').text(response.data.message)
                }
                $('body').LoadingOverlay('hide');
            }).catch((errors) => {
                $('body').LoadingOverlay('hide');
                if (errors?.response?.data?.message) {
                    alert(errors?.response?.data?.message);
                } else {
                    alert("Error");
                }
            });
        },
        closeModalSection: function () {
            var audio = document.getElementById(
                "main_audio_section"
            );
            if(audio) {
                audio.pause();
            }
            $('#modal_section').modal('hide');
        },
        closeModalViewSection: function () {
            var audio = document.getElementById(
                "main_audio_view"
            );
            if(audio) {
                audio.pause();
            }
            $('#modal_view_passage').modal('hide');
        },
        deleteSection: function (sectionId) {
            var self = this;
            var confirmDelete = confirm("Are you sure you want to delete?");
            if (confirmDelete) {
                var self = this;
                axios.defaults.baseURL = baseUrl;
                axios.defaults.headers.common.authorization =
                    localStorage.getItem("trial_test_token");
                $("body").LoadingOverlay("show");
                axios
                    .post("/admin/trial-test/delete-section", {
                        section_id: sectionId,
                        topic_id: self.topic_id
                    })
                    .then((response) => {
                        if (response.data.code === "10000") {
                            self.tab_active = 0;
                            self.initData();
                        }
                        $("body").LoadingOverlay("hide");
                    })
                    .catch((errors) => {
                        $("body").LoadingOverlay("hide");
                        if (errors?.response?.data?.message) {
                            alert(errors?.response?.data?.message);
                        } else {
                            alert("Error");
                        }
                    });
            }
        },
        changeFile: function (event){
            if(event.target.files){
                let file = event.target.files[0];
                if(file){
                    if(!file.name.match(/\.(mp3|m4a|wma|wav|aac|ogg)$/)){
                        alert('Please choose file format to audio');
                        return;
                    }
                    this.callApiUpFile(file);
                }else{
                    alert('file not found');
                }

            }
        },
        deleteFile: function (){
            this.audio_main = '';
            $('#audio_main').val('');
        },
        callApiUpFile: function (file){
            var self = this;
            const formData = new FormData();
            formData.append('file', file);
            axios.defaults.baseURL = baseStorageUrl;
            axios.defaults.headers.common.authorization = localStorage.getItem('trial_test_token');
            $('body').LoadingOverlay('show');
            axios.post( '/admin/lib-test/upload', formData)
                .then((response) => {
                    if(response.data.code === '10000'){
                        var dataFile = response.data.data;
                        if(dataFile) {
                            self.audio_main = dataFile;
                        }else{
                            alert('upload file fail');
                        }
                        $('body').LoadingOverlay('hide');
                    }else{
                        $('body').LoadingOverlay('hide');
                        return false
                    }
                })
                .catch((errors) => {
                    $('body').LoadingOverlay('hide');
                    console.log(errors); // Errors
                    return false;
                });
        }
    },
});
