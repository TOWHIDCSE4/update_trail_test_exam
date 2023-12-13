const app_create_question = new Vue({
    el: '#page_create_question_trial_test',
    data: {
        type_question: 1,
        title_question: '',
        voice_title: false,
        point_question: 1,
        word_minimum: null,
        array_sort_qna : [],
        array_sort_typing : [],
        question_id: question_id,
        layout_picture_check: 0,
        array_type_question : [
            {
                title: 'Drag&Drop',
                value: 1,
            },
            {
                title: 'Q&A',
                value: 2,
            },
            {
                title: 'Matching',
                value: 3,
            },
            {
                title: 'Typing',
                value: 4,
            },
            {
                title: 'Dropdown',
                value: 5,
            }
        ],
        category_question: 1,
        array_category_question : [
            {
                title: 'Vocabulary',
                value: 1,
            },
            {
                title: 'Reading',
                value: 2,
            },
            {
                title: 'Writing',
                value: 3,
            },
            {
                title: 'Grammar',
                value: 4,
            },
            // {
            //     title: 'Listening',
            //     value: 5,
            // },
            {
                title: 'IELTS-Writing',
                value: 6,
            }

        ],
        value_category: {
            'Vocabulary': 1,
            'Reading': 2,
            'Writing': 3,
            'Grammar': 4,
            // 'Listening': 5,
            'IELTS_Writing': 6
        },
        title_type_question : {
            'sort': 1,
            'choose': 2,
            'matching': 3,
            'typing': 4,
            'dropdown': 5,
        },
        file_image_question: '',
        file_audio_question: '',
        file_audio_title: '',
        content_question_main: '',
        voice_content_main: false,
        correct_answer_sort: '',
        incorrect_answer_sort: '',
        voice_answer: false,
        list_qna_question: [],
        list_typing_correct_answer:[],
        list_question_matching: [],
        list_dropdown: [
            {
                content: '',
                value: 1,
                description: ''
            }
        ],
        correct_answer_dropdown: '',
        flag_show_dropdown: false,
    },
    components: {
    },
    mounted() {
    },
    created(){
        $('#page_create_question_trial_test').hide();
        var component_qna = {
                'id' : '',
                'order': 1,
                'content' : '',
                'voice_content': false,
                'correct': [
                    {'subCorrect': ''}
                ],
                'incorrect': [
                    {'ic':''},
                    {'ic':''},
                    {'ic':''}
                ],
                'voice_answer': false
        };
        this.list_qna_question.push(component_qna);
        this.list_typing_correct_answer.push({ 'content': '', 'order': 1});
        var numberQuestionMatching = 5;
        for (var i = 1; i <= numberQuestionMatching; i++ ) {
            var component_matching = {
                    'id' : '',
                    'tab_a': 1,
                    'image_url_a': '',
                    'text_content_a': '',
                    'voice_text_content_a': false,
                    'tab_b': 1,
                    'image_url_b': '',
                    'text_content_b': '',
                    'audio_url': '',
            };
            this.list_question_matching.push(component_matching);
        }
        this.initData();
    },
    methods: {
        switchTabMatchQuestion: function (type, index){
            switch (type) {
                case 'text_match_a':
                    this.list_question_matching[index].tab_a = 1;
                    break;
                case 'image_match_a':
                    this.list_question_matching[index].tab_a = 2;
                    break;
                case 'text_match_b':
                    this.list_question_matching[index].tab_b = 1;
                    break;
                case 'image_match_b':
                    this.list_question_matching[index].tab_b = 2;
                    break;
            }
        },
        initData: function () {
            var self = this;
            if (self.question_id) {
                axios.defaults.baseURL = baseUrl;
                axios.defaults.headers.common.authorization = localStorage.getItem('trial_test_token');
                $('body').LoadingOverlay('show');
                axios.post('/admin/trial-test/data-detail-question', {
                    question_id: self.question_id
                }).then((response) => {
                    if (response.data.code === '10000') {
                        if(response.data.data) {
                            self.title_question = response.data.data.title;
                            self.voice_title = response.data.data.voice_for_title == 1 ? true : false;
                            self.point_question = response.data.data.scores;
                            self.type_question = response.data.data.main_type;
                            self.category_question = response.data.data.category;
                            self.file_audio_title = response.data.data.audio_title ?? '';
                            if(self.category_question == self.value_category.IELTS_Writing){
                                $('#content_ielts_writing').val(response.data.data.content_main_text);
                                $(tinymce.get('content_ielts_writing').getBody()).html(response.data.data.content_main_text);
                                self.word_minimum = response.data.data.word_minimum ?? null;
                            }
                            if(self.type_question !==  self.title_type_question.matching && self.category_question !== self.value_category.IELTS_Writing){
                                self.file_audio_question = response.data.data.content_main_audio ?? '';
                                self.file_image_question = response.data.data.content_main_picture ?? '';
                                self.layout_picture_check = response.data.data.picture_bellow_text === 1 ? true : false;
                                self.content_question_main = response.data.data.content_main_text;
                                self.voice_content_main = response.data.data.voice_for_content_main == 1 ? true : false;
                            }
                        }
                        if(self.category_question !== self.value_category.IELTS_Writing) {
                            switch (response.data.data.main_type) {
                                case self.title_type_question.sort : {
                                    self.correct_answer_sort = response.data.data.correct_answer;
                                    self.incorrect_answer_sort = response.data.data.incorrect_answer;
                                        self.voice_answer = response.data.data.voice_for_answer == 1 ? true : false;
                                    break;
                                }
                                case self.title_type_question.matching : {
                                    if (response.data.dataMatch && response.data.dataMatch.length > 0) {
                                        self.list_question_matching = [];
                                        response.data.dataMatch.forEach(function (value) {
                                            self.list_question_matching.push({
                                                'id': value.id,
                                                'tab_a': (value.content_text_a || (!value.content_text_a && !value.picture_url_a)) ? 1 : 2,
                                                'image_url_a': value.picture_url_a,
                                                'text_content_a': value.content_text_a,
                                                'voice_text_content_a': value.voice_for_content_text_a == 1 ? true : false,
                                                'tab_b': (value.content_text_b || (!value.content_text_b && !value.picture_url_b)) ? 1 : 2,
                                                'image_url_b': value.picture_url_b,
                                                'text_content_b': value.content_text_b,
                                                'audio_url': value.audio_url,
                                            });
                                        });
                                        if (response.data.dataMatch && response.data.dataMatch.length < 5) {
                                            for (var i = 1; i <= (5 - response.data.dataMatch.length); i++)
                                                self.list_question_matching.push({
                                                    'id': '',
                                                    'tab_a': 1,
                                                    'image_url_a': '',
                                                    'text_content_a': '',
                                                    'voice_text_content_a': false,
                                                    'tab_b': 1,
                                                    'image_url_b': '',
                                                    'text_content_b': '',
                                                    'audio_url': '',
                                                });
                                        }
                                    }
                                    break;
                                }
                                case self.title_type_question.choose : {
                                    if (response.data.dataQna && response.data.dataQna.length > 0) {
                                        self.list_qna_question = [];
                                        response.data.dataQna.forEach(function (value) {
                                            var arraySubCorrectTemp = value.correct_answer.split("|");
                                            var arrSubCorrect = [];
                                            arraySubCorrectTemp.forEach(function (subValue) {
                                                arrSubCorrect.push({
                                                    'subCorrect': subValue
                                                })
                                            })
                                            var arrayIncorrectTemp = value.incorrect_answer.split("|");
                                            var arrIncorrect = [];
                                            arrayIncorrectTemp.forEach(function (subValue) {
                                                arrIncorrect.push({
                                                    'ic': subValue
                                                })
                                            })
                                            self.list_qna_question.push(
                                                {
                                                    'id': value.id,
                                                    'order': value.order,
                                                    'content': value.sub_question,
                                                    'voice_content': value.voice_for_sub_question == 1 ? true : false,
                                                    'correct': arrSubCorrect,
                                                    'incorrect': arrIncorrect,
                                                    'voice_answer': value.voice_for_answer == 1 ? true : false,
                                                }
                                            )
                                        })
                                    }
                                    break;
                                }
                                case self.title_type_question.typing : {
                                    if (response.data.dataTypingCorrect && response.data.dataTypingCorrect.length > 0) {
                                        self.list_typing_correct_answer = [];
                                        response.data.dataTypingCorrect.forEach(function (value, index) {
                                            self.list_typing_correct_answer.push({'content': value, 'order': (index + 1)})
                                        })
                                    }
                                    break;
                                }
                                case self.title_type_question.dropdown : {
                                    if (response.data.data.dropdown_list) {
                                        self.list_dropdown = JSON.parse(response.data.data.dropdown_list);
                                        self.flag_show_dropdown = response.data.data.flag_show_dropdown == 1 ? true : false;
                                        self.correct_answer_dropdown = response.data.data.correct_answer;
                                    }

                                    break;
                                }
                            }
                        }
                    }
                    if(response.data.data.main_type === self.title_type_question.choose) {
                        setTimeout(function () {
                            self.refreshSort('qna');
                        }, 200);
                    }
                    if(response.data.data.main_type === self.title_type_question.typing) {
                        setTimeout(function () {
                            self.refreshSort('typing');
                        }, 200);
                    }
                    $('#page_create_question_trial_test').show();
                    $('body').LoadingOverlay('hide');
                }).catch((errors) => {
                    $('#page_create_question_trial_test').show();
                    $('body').LoadingOverlay('hide');
                    if (errors?.response?.data?.message) {
                        alert(errors?.response?.data?.message);
                    } else {
                        alert("Error");
                    }
                });
            }else {
                $('#page_create_question_trial_test').show();
            }
        },
        changeFile: function (event, type, index){
            if(event.target.files){
                let file = event.target.files[0];
                if(file){
                    switch (type) {
                        case 'audio_title' :
                        case 'audio_question' :
                        case 'file_audio_match' :
                            if(!file.name.match(/\.(mp3|m4a|wma|wav|aac|ogg)$/)){
                                alert('Please choose file format to audio');
                                return;
                            }
                            break
                        case 'image_question' :
                        case 'file_image_match_a' :
                        case 'file_image_match_b' :
                            if(!file.name.match(/\.(jpg|jpeg|png)$/)){
                                alert('Please choose .jpg|jpeg|png format to image');
                                return;
                            }
                            break
                    }
                   this.callApiUpFile(file, type, index);
                }else{
                    alert('file not found');
                }

            }
        },
        deleteFile: function (type, index){
            switch (type) {
                case 'audio_title' :
                    this.file_audio_title = '';
                    $('#audio_title').val('');
                    break
                case 'image_question' :
                    this.file_image_question = '';
                    $('#image_question').val('');
                    break
                case 'audio_question' :
                    this.file_audio_question = '';
                    $('#audio_question').val('');
                    break
                case 'file_image_match_a' :
                    this.list_question_matching[index].image_url_a = '';
                    $('#file_image_match_a'+index).val('');
                    break
                case 'file_image_match_b' :
                    this.list_question_matching[index].image_url_b = '';
                    $('#file_image_match_b'+index).val('');
                    break
                case 'file_audio_match' :
                    this.list_question_matching[index].audio_url = '';
                    $('#file_audio_match'+index).val('');
                    break
            }
        },
         callApiUpFile: function (file, type, index){
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
                            switch (type) {
                                case 'audio_title' :
                                    self.file_audio_title = dataFile;
                                    break
                                case 'image_question' :
                                    self.file_image_question = dataFile;
                                    break
                                case 'audio_question' :
                                    self.file_audio_question = dataFile;
                                    break
                                case 'file_image_match_a' :
                                    self.list_question_matching[index].image_url_a = dataFile;
                                    break
                                case 'file_image_match_b' :
                                    self.list_question_matching[index].image_url_b = dataFile;
                                    break
                                case 'file_audio_match' :
                                    self.list_question_matching[index].audio_url = dataFile;
                                    break
                            }
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
        },
        saveQuestion: function (){
            var self = this;
            var checkValidate = self.validateSaveQuestion();
            if (!checkValidate){
                return;
            }
            const formData = new FormData();
            var contentMain = self.content_question_main;
            if(self.category_question == self.value_category.IELTS_Writing){
                contentMain = $('#content_ielts_writing').val();
                formData.append('word_minimum', self.word_minimum ?? null);
            }
            formData.append('question_id', self.question_id);
            formData.append('topic_id', topic_id);
            formData.append('section_id', section_id);
            formData.append('title', self.title_question);
            formData.append('voice_title', self.voice_title ? 1 : 0);
            formData.append('scores', self.point_question);
            formData.append('type_question', self.type_question);
            formData.append('category', self.category_question);
            formData.append('url_audio_title', self.file_audio_title ?? '');
            if(self.type_question !==  self.title_type_question.matching){
                formData.append('url_main_audio', self.file_audio_question ?? '');
                formData.append('url_main_image', self.file_image_question ?? '');
                formData.append('picture_bellow_text', self.layout_picture_check);
                formData.append('content_question_main', contentMain ?? '');
                formData.append('voice_content_main', self.voice_content_main ? 1 : 0);
            }
            switch (self.type_question) {
                case self.title_type_question.sort :
                    formData.append('correct_answer', self.correct_answer_sort ?? '');
                    formData.append('incorrect_answer', self.incorrect_answer_sort ?? '');
                    formData.append('voice_answer', self.voice_answer ? 1 : 0);
                    break;
                case self.title_type_question.matching :
                    var arrayRemoveNull = [];
                    self.list_question_matching.forEach(function (value, index){
                       if(value.tab_a == 1 && value.tab_b == 1){
                           if(!value.text_content_a && !value.text_content_b && !value.audio_url){
                               arrayRemoveNull.push(index);
                           }
                       }else if(value.tab_a == 1 && value.tab_b == 2){
                           if(!value.text_content_a && !value.image_url_b && !value.audio_url){
                               arrayRemoveNull.push(index);
                           }
                       }else if(value.tab_a == 2 && value.tab_b == 1){
                           if(!value.image_url_a && !value.text_content_b && !value.audio_url){
                               arrayRemoveNull.push(index);
                           }
                       }else if(value.tab_a == 2 && value.tab_b == 2){
                           if(!value.image_url_a && !value.image_url_b && !value.audio_url){
                               arrayRemoveNull.push(index);
                           }
                       }
                    });
                    arrayRemoveNull.forEach(function (value, index){
                        self.list_question_matching.splice(value-index, 1);
                    })
                    formData.append('array_match_question', JSON.stringify(self.list_question_matching));
                    break;
                case self.title_type_question.choose :
                    if(self.array_sort_qna && self.array_sort_qna.length > 0){
                        formData.append('array_qna_question', JSON.stringify(self.array_sort_qna));
                    }else{
                        formData.append('array_qna_question', JSON.stringify(self.list_qna_question));
                    }
                    break;
                case self.title_type_question.typing :
                    if(self.array_sort_typing && self.array_sort_typing.length > 0){
                        formData.append('array_typing_answer_correct', JSON.stringify(self.array_sort_typing));
                    }else{
                        formData.append('array_typing_answer_correct', JSON.stringify(self.list_typing_correct_answer));
                    }
                    break;
                case self.title_type_question.dropdown :
                    console.log(self.list_dropdown)
                    formData.append('dropdown', self.list_dropdown ? JSON.stringify(self.list_dropdown)  : '');
                    formData.append('flag_show_dropdown', self.flag_show_dropdown ? 1 : 0);
                    formData.append('correct_answer', self.correct_answer_dropdown ?? '');
                    break;
            }
            axios.defaults.baseURL = baseUrl;
            axios.defaults.headers.common.authorization = localStorage.getItem('trial_test_token');
            $('body').LoadingOverlay('show');
            axios.post( '/admin/trial-test/save-question', formData)
                .then((response) => {
                    if(response.data.code === '10000'){
                        if(topic_id) {
                            window.location.href = '/admin/trial-test/list-questions/'+topic_id;
                        }else{
                            alert('save fail');
                        }
                    }
                    $('body').LoadingOverlay('hide');
                }).catch((errors) => {
                    $('body').LoadingOverlay('hide');
                    if (errors?.response?.data?.message) {
                        alert(errors?.response?.data?.message);
                    } else {
                        alert("Save failed");
                    }
                });
        },
        addComponent: function (type, index = null){
            switch (type) {
                case 'qna': {
                    this.list_qna_question.push({
                        'id': '',
                        'order': this.list_qna_question && this.list_qna_question.length >= 0 ? (this.list_qna_question.length + 1) : 1,
                        'content': '',
                        'voice_content': false,
                        'correct': [
                            {'subCorrect': ''}
                        ],
                        'incorrect': [
                            {'ic': ''},
                            {'ic': ''},
                            {'ic': ''}
                        ],
                        'voice_answer': false,
                    });
                    if (this.array_sort_qna && this.array_sort_qna > 0) {
                        this.list_qna_question.push({
                            'id': '',
                            'order': this.list_qna_question && this.list_qna_question.length >= 0 ? (this.list_qna_question.length + 1) : 1,
                            'content': '',
                            'voice_content': false,
                            'correct': [
                                {'subCorrect': ''}
                            ],
                            'incorrect': [
                                {'ic': ''},
                                {'ic': ''},
                                {'ic': ''}
                            ],
                            'voice_answer': false,
                        });
                    }
                    this.refreshSort('qna');
                    break;
                }
                case 'typing': {
                    this.list_typing_correct_answer.push({
                        'content': '',
                        'order': this.list_typing_correct_answer && this.list_typing_correct_answer.length >= 0 ? (this.list_typing_correct_answer.length + 1) : 1
                    });
                    if (this.array_sort_typing && this.array_sort_typing > 0) {
                        this.array_sort_typing.push({
                            'content': '',
                            'order': this.array_sort_typing && this.array_sort_typing.length >= 0 ? (this.array_sort_typing.length + 1) : 1
                        });
                    }
                    this.refreshSort('typing');
                    break;
                }
                case 'qna-correct-answer': {
                    this.list_qna_question[index].correct.push ({
                        'subCorrect': ''
                    })
                    break;
                }
                case 'qna-incorrect-answer': {
                    this.list_qna_question[index].incorrect.push ({
                        'ic': ''
                    })
                    break;
                }
                case 'dropdown': {
                    this.list_dropdown.push ({
                        content: '',
                        value: index+1,
                        description: ''
                    })
                    break;
                }
            }
        },
        removeComponent: function (type, index = null, order = null, indexAnswer = null){
            var self = this;
            switch (type) {
                case 'qna': {
                    var indexNew = null;
                    self.array_sort_qna.forEach(function (value, indexQna){
                        if(value.order == order){
                            indexNew = indexQna;
                        }
                    })
                    self.list_qna_question.splice(index, 1);
                    self.array_sort_qna.splice(indexNew, 1);
                    break;
                }
                case 'typing': {
                    var indexNew = null;
                    self.array_sort_typing.forEach(function (value, indexQna){
                        if(value.order == order){
                            indexNew = indexQna;
                        }
                    })
                    self.list_typing_correct_answer.splice(index, 1);
                    self.array_sort_typing.splice(indexNew, 1);
                    break;
                }
                case 'qna-correct-answer': {
                    self.list_qna_question[index].correct.splice(indexAnswer, 1);
                    break;
                }
                case 'qna-incorrect-answer': {
                    self.list_qna_question[index].incorrect.splice(indexAnswer, 1);
                    break;
                }
                case 'dropdown': {
                    self.list_dropdown.splice(index, 1);
                    var arrayDropdown = [];
                    self.list_dropdown.forEach(function (value, index){
                        arrayDropdown.push(
                            {
                                content: value.content,
                                value: index+1,
                                description: value.description,
                            }
                        )
                    })
                    self.list_dropdown = arrayDropdown;
                    break;
                }
            }
        },
        resetPoint: function (){
            if(!this.point_question || this.point_question < 0) {
                this.point_question = 1;
            }
            if((this.word_minimum && this.word_minimum < 0)) {
                this.word_minimum = null;
            }
        },
        validateSaveQuestion: function (){
            var self = this;
            var flagError = 0;
            if(!self.title_question){
                $('#title_error').text(titleError);
                flagError = 1;
            } else {
                $('#title_error').text('');
            }
            if(self.category_question == self.value_category.IELTS_Writing){
                if(!$('#content_ielts_writing').val()){
                    $('#content_ielts_writing_error').text(titleError);
                    flagError = 1;
                } else {
                    $('#content_ielts_writing_error').text('');
                }
            }else {
                if(self.type_question ===  self.title_type_question.sort || self.type_question ===  self.title_type_question.typing){
                    if(!self.file_audio_question && !self.file_image_question && !self.content_question_main){
                        $('#content_question_main_error').text(titleError);
                        flagError = 1;
                    } else {
                        $('#content_question_main_error').text('');
                    }
                }
                switch (self.type_question) {
                    case self.title_type_question.sort :
                        if(!self.content_question_main){
                            $('#content_question_main_error').text(titleError);
                            flagError = 1;
                        } else {
                            $('#content_question_main_error').text('');
                            var numContent = (self.content_question_main.split("??").length - 1);
                            var numCorrect = (self.correct_answer_sort.split("|").length);
                            if(numContent != numCorrect){
                                $('#content_question_main_error').text('Please enter the number of boxes to fill in equal to the number of correct answers');
                                flagError = 1;
                            }
                        }
                        if(!self.correct_answer_sort){
                            $('#correct_answer_sort_error').text(titleError);
                            flagError = 1;
                        } else {
                            $('#correct_answer_sort_error').text('');
                        }
                        break;
                    case self.title_type_question.matching :
                        var flagMatch = 0;
                        var flagMatchError = 0;
                        var flagMatchNoError = 0;
                        var flagTitle = flagError;
                        self.list_question_matching.forEach(function (quesMatch, indexMatch){
                            if(quesMatch.tab_a == 1){
                                if(quesMatch.tab_b == 1){
                                    if(!quesMatch.text_content_a && !quesMatch.text_content_b && !quesMatch.audio_url){
                                        flagMatch++;
                                    }else{
                                        if( (quesMatch.text_content_a && !quesMatch.text_content_b && !quesMatch.audio_url) || (!quesMatch.text_content_a && (quesMatch.text_content_b || quesMatch.audio_url))){
                                            if(flagMatchError === 0){
                                                $('#question_match_error' + indexMatch).text(titleError);
                                                flagMatchError = 1;
                                                flagError = 1;
                                            }else{
                                                $('#question_match_error' + indexMatch).text('');
                                            }
                                        } else {
                                            $('#question_match_error' + indexMatch).text('');
                                            flagMatchNoError++;
                                        }
                                    }
                                }else if (quesMatch.tab_b == 2){
                                    if(!quesMatch.text_content_a && !quesMatch.image_url_b && !quesMatch.audio_url){
                                        flagMatch++;
                                    }else{
                                        if((quesMatch.text_content_a && !quesMatch.image_url_b && !quesMatch.audio_url) || (!quesMatch.text_content_a && (quesMatch.image_url_b || quesMatch.audio_url))){
                                            if(flagMatchError === 0){
                                                $('#question_match_error' + indexMatch).text(titleError);
                                                flagMatchError = 1;
                                                flagError = 1;
                                            }else{
                                                $('#question_match_error' + indexMatch).text('');
                                            }
                                        } else {
                                            $('#question_match_error' + indexMatch).text('');
                                            flagMatchNoError++;
                                        }
                                    }
                                }
                            } else if(quesMatch.tab_a == 2){
                                if(quesMatch.tab_b == 1){
                                    if(!quesMatch.image_url_a && !quesMatch.text_content_b && !quesMatch.audio_url){
                                        flagMatch++;
                                    }else{
                                        if( (quesMatch.image_url_a && !quesMatch.text_content_b && !quesMatch.audio_url) || (!quesMatch.image_url_a && (quesMatch.text_content_b || quesMatch.audio_url))){
                                            if(flagMatchError === 0){
                                                $('#question_match_error' + indexMatch).text(titleError);
                                                flagMatchError = 1;
                                                flagError = 1;
                                            }else{
                                                $('#question_match_error' + indexMatch).text('');
                                            }
                                        } else {
                                            $('#question_match_error' + indexMatch).text('');
                                            flagMatchNoError++;
                                        }
                                    }
                                }else if (quesMatch.tab_b == 2){
                                    if(!quesMatch.image_url_a && !quesMatch.image_url_b && !quesMatch.audio_url){
                                        flagMatch++;
                                    }else{
                                        if( (quesMatch.image_url_a && !quesMatch.image_url_b && !quesMatch.audio_url) || (!quesMatch.image_url_a && (quesMatch.image_url_b || !uesMatch.audio_url))){
                                            if(flagMatchError === 0){
                                                $('#question_match_error' + indexMatch).text(titleError);
                                                flagMatchError = 1;
                                                flagError = 1;
                                            }else{
                                                $('#question_match_error' + indexMatch).text('');
                                            }
                                        } else {
                                            $('#question_match_error' + indexMatch).text('');
                                            flagMatchNoError++;
                                        }
                                    }
                                }
                            }
                        });
                        if(flagMatch === 5){
                            $('#question_match_error').text(titleError);
                            flagError = 1;
                        }else{
                            $('#question_match_error').text('');
                            if(flagMatchError === 1 && flagMatchNoError > 0){
                                self.list_question_matching.forEach(function (quesMatch, indexMatch){
                                    $('#question_match_error' + indexMatch).text('');
                                })
                                if(flagTitle === 0){
                                    flagError = 0
                                }
                            }

                        }

                        break;
                    case self.title_type_question.choose :
                        self.list_qna_question.forEach(function (value, index){
                            if(!value.content){
                                $('#question_qna_error'+index).text(titleError);
                                flagError = 1;
                            }else {
                                $('#question_qna_error'+index).text('');
                            }
                            var flagSc = 1;
                            value.correct.forEach(function (scValue, icIndex){
                                if(scValue.subCorrect) {
                                    $('#correct_answer_qna_error' + index + '_0').text(titleError);
                                    flagSc = 0;
                                }
                            })
                            if(flagSc == 1){
                                flagError = 1;
                                $('#correct_answer_qna_error'+index+'_0').text(titleError);
                            }else{
                                $('#correct_answer_qna_error'+index+'_0').text('');
                            }
                            var flagIc  = 1;
                            value.incorrect.forEach(function (icValue, icIndex){
                                if(icValue.ic) {
                                    $('#incorrect_answer_qna_error' + index + '_0').text(titleError);
                                    flagIc = 0;
                                }
                            })
                            if(flagIc == 1){
                                flagError = 1;
                                $('#incorrect_answer_qna_error'+index+'_0').text(titleError);
                            }else{
                                $('#incorrect_answer_qna_error'+index+'_0').text('');
                            }
                        });
                        break;
                    case self.title_type_question.typing :
                        if(!self.content_question_main){
                            $('#content_question_main_error').text(titleError);
                            flagError = 1;
                        } else {
                            $('#content_question_main_error').text('');
                            var numContent = (self.content_question_main.split("??").length - 1);
                            var numCorrect = self.list_typing_correct_answer.length;
                            if(numContent != numCorrect){
                                $('#content_question_main_error').text('Please enter the number of boxes to fill in equal to the number of correct answers');
                                flagError = 1;
                            }
                        }
                        self.list_typing_correct_answer.forEach(function (value, index){
                            if(!value.content){
                                $('#answer_typing_error'+index).text(titleError);
                                flagError = 1;
                            } else {
                                $('#answer_typing_error'+index).text('');
                            }
                        });
                        break;
                    case self.title_type_question.dropdown :
                        if(!self.content_question_main){
                            $('#content_question_main_error').text(titleError);
                            flagError = 1;
                        } else {
                            $('#content_question_main_error').text('');
                            var numContent = (self.content_question_main.split("??").length - 1);
                            var numCorrect = (self.correct_answer_dropdown.split("|").length);
                            if(numContent !== numCorrect){
                                $('#content_question_main_error').text('Please enter the number of boxes to fill in equal to the number of correct answers');
                                flagError = 1;
                            }
                        }
                        if(!self.correct_answer_dropdown){
                            $('#correct_answer_dropdown_error').text(titleError);
                            flagError = 1;
                        } else {
                            $('#correct_answer_dropdown_error').text('');
                        }
                        console.log(self.list_dropdown)
                        self.list_dropdown.forEach(function (value, index){
                            console.log(value.content)
                            if(!value.content) {
                                $('#question_dropdown_error' + index).text(titleError);
                                flagError = 1;
                            }
                        })
                        break;
                }
            }
            if (flagError === 1){
                return false;
            } else {
                return true;
            }
        },
        refreshSort: function (type){
            var self = this;
            if(type === 'qna'){
                    $('#sort_qna_question').sortable({
                        handle: '.sort-qna-question-item',
                        cancel: '',
                        update: function (c, d) {
                            var a = $(this).sortable('toArray', {attribute: 'data-id'});
                            self.array_sort_qna = [];
                            a.forEach(function (value){
                                self.array_sort_qna.push(self.list_qna_question[value])
                            });
                        }
                    }).disableSelection();
            } else if(type === 'typing') {
                $('#sort_typing_question').sortable({
                    handle: '.sort-typing-question-item',
                    cancel: '',
                    update: function (c, d) {
                        var b = $(this).sortable('toArray', {attribute: 'data-id'});
                        self.array_sort_typing = [];
                        b.forEach(function (value){
                            self.array_sort_typing.push(self.list_typing_correct_answer[value])
                        });
                    }
                });
            }
        }
    }
});
