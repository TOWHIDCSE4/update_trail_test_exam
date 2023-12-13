// Constant
const TEST_TYPE = {
    NORMAL: 1,
    STAFF_PRE_TEST: 2,
};

const DISPLAY_TYPE = {
    TEST: "test",
    RESULT: "result",
};

const TOPIC_TEST_TYPE = {
    COMMON: "COMMON",
    IELTS_GRAMMAR: "IELTS_GRAMMAR",
    IELTS_WRITING: "IELTS_WRITING",
    IELTS_LISTENING: "IELTS_LISTENING",
    IELTS_READING: "IELTS_READING",
};

// Draw a line - START
var targetOption = {
    anchor: "TopCenter",
    maxConnections: 1,
    isSource: false,
    isTarget: true,
    reattach: true,
    endpoint: "Blank",
    connector: [
        "Bezier",
        {
            curviness: 50,
        },
    ],
    setDragAllowedWhenFull: true,
};

var sourceOption = {
    tolerance: "touch",
    anchor: "BottomCenter",
    maxConnections: 1,
    isSource: true,
    isTarget: false,
    reattach: true,
    endpoint: "Blank",
    connector: [
        "Bezier",
        {
            curviness: 50,
        },
    ],
    setDragAllowedWhenFull: true,
};

// var questionEndpoints = []; // 'source' and 'target' endpoints
var connectObj = {
    source: null,
    target: null,
};
// Draw a line - END

var trial_test = new Vue({
    el: "#trial_test",
    data: {
        items: {
            isResult: false,
            questions: [],
        },
        questionEndpoints: [],
        showResultAfterTest: true,
        isShowMatchingResult: false,
        arrIdxStepQA: [],
        currentIdxSection: 0,
        arrIdxStepSection: [],
        testCode: null,
        testType: null,
        idBooking: null,
        audioPlayer: {},
        testResultId: null,
        scoreScaleReceived: null,
        resultTestIelts: null,
        resultType: null,
        idxQuestionClickMatchingResult: null,
        idSource: null,
        sourceTagId: null,
        idxContentSource: null,
        idTarget: null,
        targetTagId: null,
        arrFirstMatching: [],
        arrIdxQShowResult: [],
        paintStyle: null,
        resizeObserver: null,
        intervalIdStartTimer: null,
        passageAndQuestionBoxHeight: 0,
        listeningSectionAudioHeight: 0,
        readingSectionHeight: 0
    },
    created: function () {
        var self = this;
        self.initData();
        self.resizeObserver = new ResizeObserver(self.loadMatchingResult);
        self.listeningSectionAudioHeight = $(".listening_section_audio").height() + 32
        self.readingSectionHeight = $(document).height()-$(".box_action_footer").height()-$(".box_remain_time").height()-$(".section_name").height()-265
    },
    methods: {
        initData: function () {
            var self = this;
            const vars = self.getUrlVars();
            console.log(vars);

            const id = vars["id"];
            const code = vars["code"];
            const type = vars["type"];
            const testType = vars["test_type"];

            self.testCode = code;
            self.testType = testType;
            self.idBooking = vars["id_booking"];

            let url = "";
            let params = {};
            let queryParamsUrl = "";
            // const trialTestToken = localStorage.getItem("trial_test_token");
            if (code && type == DISPLAY_TYPE.RESULT) {
                url = "/student/get-test-results";
                params.test_code = code;
                queryParamsUrl += `?code=${code}&type=${type}`;
                axios.defaults.baseURL = baseUrl;
                // axios.defaults.headers.common.authorization = trialTestToken;
            } else if (code && testType == TEST_TYPE.STAFF_PRE_TEST) {
                url = "/student/start-pre-test";
                params.test_code = code;
                params.test_type = testType;
                queryParamsUrl += `?code=${code}&test_type=${testType}`;
                axios.defaults.baseURL = baseApiUrl;
                // axios.defaults.headers.common.authorization = trialTestToken;
                // } else if (code && type == "preview") {
                //     url = "/student/get-trial-test-preview";
                //     params.test_code = code;
                //     axios.defaults.baseURL = baseUrl;
            } else if (code && type == DISPLAY_TYPE.TEST) {
                url = "/student/start-test";
                params.test_code = code;
                queryParamsUrl += `?code=${code}&type=${type}`;
                axios.defaults.baseURL = baseApiUrl;
                // axios.defaults.headers.common.authorization = trialTestToken;
            }

            $("body").LoadingOverlay("show");
            if (url) {
                window.history.pushState("", "", queryParamsUrl);

                axios
                    .get(url, {
                        params: params,
                    })
                    .then(function (response) {
                        console.log(response);
                        if (response.data.code === "10000") {
                            if (
                                response.data.data.isResult &&
                                response.data.data.topic_test_type ==
                                    TOPIC_TEST_TYPE.IELTS_WRITING
                            ) {
                                const newSections =
                                    response.data.data.sections.map((section) => {
                                        const newQuestions = section.questions.map((item) => {
                                            const tmp = {
                                                ...item,
                                                word_count: self.wordCount(
                                                    item.answer
                                                ),
                                            };
                                            return tmp;
                                        })

                                        return {
                                            ...section,
                                            questions: newQuestions
                                        }
                                    });
                                self.items = {
                                    ...response.data.data,
                                    sections: newSections
                                };
                            } else {
                                self.items = response.data.data;
                            }
                            console.log(">>> self.items: ", self.items);
                            if (!response.data.data.isResult) {
                                if (response.data.test_time) {
                                    var time = response.data.test_time;
                                    self.startTimer(time);
                                } else {
                                    $(".modal-time-out").modal("show");
                                }
                            }


                            $("#trial_test").show();
                            if (
                                response.data.data.topic_test_type == TOPIC_TEST_TYPE.IELTS_READING ||
                                response.data.data.topic_test_type == TOPIC_TEST_TYPE.IELTS_LISTENING
                            ) {
                                self.passageAndQuestionBoxHeight = self.readingSectionHeight

                                if (response.data.data.topic_test_type == TOPIC_TEST_TYPE.IELTS_LISTENING) {
                                    self.passageAndQuestionBoxHeight = self.readingSectionHeight - self.listeningSectionAudioHeight
                                }
                            }
                        } else {
                            if (response.data.message) {
                                alert(response.data.message);
                            } else {
                                alert("Error");
                            }
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                        if (error?.response?.data?.message) {
                            alert(error?.response?.data?.message);
                        } else {
                            alert("Error");
                        }
                    })
                    .then(function () {
                        $("body").LoadingOverlay("hide");
                    });
            }
        },
        loadTrack: function (questionId, audio) {
            console.log(">>>>> loadTrack");
            console.log(">>>>> questionId: ", questionId);
            console.log(">>>>> audio: ", audio);
            var self = this;
            let isPlaying = false;
            let updateTimer;

            clearInterval(updateTimer);
            self.resetValues(questionId);
            let curr_track = document.createElement("audio");
            curr_track.id = `q${questionId}-audio_player`;
            curr_track.src = audio;
            console.log(">>>>> curr_track: ", curr_track);

            curr_track.load();

            self.audioPlayer[`q${questionId}`] = {
                curr_track: curr_track,
                updateTimer: updateTimer,
                isPlaying: isPlaying,
            };

            updateTimer = setInterval(() => {
                var self = this;
                let seekPosition = 0;
                if (!isNaN(curr_track.duration)) {
                    seekPosition =
                        curr_track.currentTime * (100 / curr_track.duration);

                    $(`.q${questionId}-seek_slider`).val(seekPosition);

                    let currentMinutes = Math.floor(
                        curr_track.currentTime / 60
                    );
                    let currentSeconds = Math.floor(
                        curr_track.currentTime - currentMinutes * 60
                    );
                    let durationMinutes = Math.floor(curr_track.duration / 60);
                    let durationSeconds = Math.floor(
                        curr_track.duration - durationMinutes * 60
                    );

                    if (currentSeconds < 10) {
                        currentSeconds = "0" + currentSeconds;
                    }
                    if (durationSeconds < 10) {
                        durationSeconds = "0" + durationSeconds;
                    }
                    if (currentMinutes < 10) {
                        currentMinutes = "0" + currentMinutes;
                    }
                    if (durationMinutes < 10) {
                        durationMinutes = "0" + durationMinutes;
                    }

                    self.audioPlayer[`q${questionId}`].curr_track = curr_track;
                    $(`.q${questionId}-current-time`).text(
                        currentMinutes + ":" + currentSeconds
                    );
                    $(`.q${questionId}-total-duration`).text(
                        durationMinutes + ":" + durationSeconds
                    );
                }
            }, 1000);

            self.audioPlayer[`q${questionId}`].updateTimer = updateTimer;

            console.log(">>>>> self.audioPlayer: ", self.audioPlayer);
        },
        resetValues: function (questionId) {
            var self = this;

            $(`.q${questionId}-current-time`).text("00:00");
            $(`.q${questionId}-total-duration`).text("00:00");
            $(`.q${questionId}-seek_slider`).val(0);
        },
        playPauseTrack: function (questionId) {
            var self = this;

            if (!self.audioPlayer[`q${questionId}`].isPlaying)
                self.playTrack(questionId);
            else self.pauseTrack(questionId);
        },
        playTrack: function (questionId) {
            var self = this;

            self.audioPlayer[`q${questionId}`].curr_track.play();
            self.audioPlayer[`q${questionId}`].isPlaying = true;
            $(`.q${questionId}-playpause-track`).html(
                '<i class="fa fa-pause-circle fa-3x"></i>'
            );
        },
        pauseTrack: function (questionId) {
            var self = this;

            self.audioPlayer[`q${questionId}`].curr_track.pause();
            self.audioPlayer[`q${questionId}`].isPlaying = false;
            $(`.q${questionId}-playpause-track`).html(
                '<i class="fa fa-play-circle fa-3x"></i>'
            );
        },
        next: function (questionId) {
            var self = this;

            self.audioPlayer[`q${questionId}`].curr_track.currentTime += 5;
        },
        prev: function (questionId) {
            var self = this;

            self.audioPlayer[`q${questionId}`].curr_track.currentTime -= 5;
        },
        seekTo: function (questionId) {
            var self = this;

            let seekto =
                self.audioPlayer[`q${questionId}`].curr_track.duration *
                ($(`.q${questionId}-seek_slider`).val() / 100);
            self.audioPlayer[`q${questionId}`].curr_track.currentTime = seekto;
        },
        isUrlValid: function (str) {
            var res = str.match(
                /(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g
            );
            if (res == null) return false;
            else return true;
        },
        playAudio: function (sectionId, questionId) {
            var self = this;
            var audioLink = self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).content_main_audio;
            var checkPlay = self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).play_audio;
            self.pauseAllAudio();
            if (audioLink) {
                var audio = document.getElementById(
                    "question_main_audio" + questionId
                );
                if (checkPlay === 0) {
                    audio.load();
                    audio.play();
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].play_audio = 1;
                } else if (checkPlay === 1) {
                    audio.pause();
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].play_audio = 0;
                }
            }
        },
        playAudioSection: function (sectionId) {
            var self = this;
            var audioLink = self.items.sections.find(x => x.section_id == sectionId).audio;
            var checkPlay = self.items.sections.find(x => x.section_id == sectionId).play_audio;
            self.pauseAllAudio();
            if (audioLink) {
                var audio = document.getElementById(
                    "section_main_audio" + sectionId
                );
                if (checkPlay === 0) {
                    audio.load();
                    audio.play();
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    self.items.sections[currentIdxSection].play_audio = 1;
                } else if (checkPlay === 1) {
                    audio.pause();
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    self.items.sections[currentIdxSection].play_audio = 0;
                }
            }
        },
        playTitleAudio: function (sectionId, questionId, type = "audio_text") {
            var self = this;
            self.pauseAllAudio();
            if (type == "audio_link") {
                var audioLink = self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).audio_title;
                var checkPlay =
                    self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).title_play_audio_with_url;
                if (audioLink) {
                    var audio = document.getElementById(
                        "question_title_audio" + questionId
                    );
                    if (checkPlay === 0) {
                        audio.load();
                        audio.play();
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].title_play_audio_with_url = 1;
                    } else if (checkPlay === 1) {
                        audio.pause();
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].title_play_audio_with_url = 0;
                    }
                }
            } else {
                var title = self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).title;
                if (title) {
                    var checkPlay =
                        self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).title_play_audio;
                    if (checkPlay === 0) {
                        let utterance = self.speechText(title);
                        utterance.onend = (event) => {
                            const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].title_play_audio = 0;
                        };
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].title_play_audio = 1;
                    }
                }
            }
        },
        playContentAudio: function (sectionId, questionId) {
            var self = this;
            var checkPlay = self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).content_play_audio;
            const content = document.getElementById(
                "question-" + (questionId) + "-content"
            ).textContent;
            console.log(">>> playContentAudio, content: ", content);
            self.pauseAllAudio();
            if (content) {
                if (checkPlay === 0) {
                    let utterance = self.speechText(content);
                    utterance.onend = (event) => {
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].content_play_audio = 0;
                    };
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].content_play_audio = 1;
                }
            }
        },
        playSubQuestionAudio: function (sectionId, questionId, idxSubQ) {
            var self = this;
            var subQuestion =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).sub_questions[idxSubQ]
                    .sub_question;
            var checkPlay =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).sub_questions[idxSubQ]
                    .subQ_play_audio;
            self.pauseAllAudio();
            if (subQuestion) {
                if (checkPlay === 0) {
                    let utterance = self.speechText(subQuestion);
                    utterance.onend = (event) => {
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[
                            idxSubQ
                        ].subQ_play_audio = 0;
                    };
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[
                        idxSubQ
                    ].subQ_play_audio = 1;
                }
            }
        },
        playAnswerSubQuestionAudio: function (sectionId, questionId, idxSubQ, idxAnswer) {
            var self = this;
            var answer =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).sub_questions[idxSubQ]
                    .shuffle_answer[idxAnswer].answer;
            var checkPlay =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).sub_questions[idxSubQ]
                    .shuffle_answer[idxAnswer].answer_play_audio;
            self.pauseAllAudio();
            if (answer) {
                if (checkPlay === 0) {
                    let utterance = self.speechText(answer);
                    utterance.onend = (event) => {
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[
                            idxSubQ
                        ].shuffle_answer[idxAnswer].answer_play_audio = 0;
                    };
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[
                        idxSubQ
                    ].shuffle_answer[idxAnswer].answer_play_audio = 1;
                }
            }
        },
        playAnswerSortAudio: function (sectionId, questionId, idxAnswer, isResult) {
            console.log(">>> playAnswerSortAudio");
            var self = this;
            var answer =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).shuffle_answer[idxAnswer].answer;
            var checkPlay =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).shuffle_answer[idxAnswer]
                    .answer_play_audio;

            if (isResult) {
                answer =
                    self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).student_answers[idxAnswer]
                        .answer;
                checkPlay =
                    self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).student_answers[idxAnswer]
                        .answer_play_audio;
            }

            self.pauseAllAudio();
            console.log(
                "sort-question" +
                    (Number(questionId)) +
                    "_answer" +
                    (Number(idxAnswer) + 1)
            );
            if (answer) {
                if (checkPlay === 0) {
                    let utterance = self.speechText(answer);
                    utterance.onend = (event) => {
                        let icon_sort = document.querySelectorAll(
                            ".icon_sort-question" +
                                (Number(questionId)) +
                                "_answer" +
                                (Number(idxAnswer) + 1)
                        );
                        icon_sort.forEach((box) => {
                            box.style.display = "inline-block";
                        });
                        let icon_gif_sort = document.querySelectorAll(
                            ".icon_gif_sort-question" +
                                (Number(questionId)) +
                                "_answer" +
                                (Number(idxAnswer) + 1)
                        );
                        icon_gif_sort.forEach((box) => {
                            box.style.display = "none";
                        });

                        if (isResult) {
                            const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].student_answers[
                                idxAnswer
                            ].answer_play_audio = 0;
                        } else {
                            const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].shuffle_answer[
                                idxAnswer
                            ].answer_play_audio = 0;
                        }
                    };

                    let icon_sort = document.querySelectorAll(
                        ".icon_sort-question" +
                            (Number(questionId)) +
                            "_answer" +
                            (Number(idxAnswer) + 1)
                    );
                    icon_sort.forEach((box) => {
                        box.style.display = "none";
                    });
                    let icon_gif_sort = document.querySelectorAll(
                        ".icon_gif_sort-question" +
                            (Number(questionId)) +
                            "_answer" +
                            (Number(idxAnswer) + 1)
                    );
                    icon_gif_sort.forEach((box) => {
                        box.style.display = "inline-block";
                    });

                    if (isResult) {
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].student_answers[
                            idxAnswer
                        ].answer_play_audio = 1;
                    } else {
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].shuffle_answer[
                            idxAnswer
                        ].answer_play_audio = 1;
                    }
                }
            }
        },
        pauseAllAudio: function () {
            var self = this;
            speechSynthesis.cancel();
            document.querySelectorAll("audio").forEach((el) => el.pause());
            self.items.sections.forEach(function (section, indexSection) {
                if (section.audio) {
                    self.items.sections[indexSection].play_audio = 0;
                }

                section.questions.forEach(function (value, indexQ) {
                    if (value.main_type == 3) {
                        value.shuffle_row_1.forEach(function (valueM, indexM) {
                            self.items.sections[indexSection].questions[indexQ].shuffle_row_1[
                                indexM
                            ].play_audio = 0;
                        });
                        value.shuffle_row_2.forEach(function (valueM, indexM) {
                            self.items.sections[indexSection].questions[indexQ].shuffle_row_2[
                                indexM
                            ].play_audio = 0;
                        });
                    } else {
                        if (value.audio_title) {
                            self.items.sections[indexSection].questions[
                                indexQ
                            ].title_play_audio_with_url = 0;
                        }
                        if (value.content_main_audio) {
                            self.items.sections[indexSection].questions[indexQ].play_audio = 0;
                            self.items.sections[indexSection].questions[indexQ].title_play_audio = 0;

                            if (value.main_type == 1 || value.main_type == 2) {
                                self.items.sections[indexSection].questions[indexQ].content_play_audio = 0;
                            }

                            if (value.main_type == 2) {
                                self.items.sections[indexSection].questions[indexQ].sub_questions.forEach(
                                    function (valSubQ, idxSubQ) {
                                        self.items.sections[indexSection].questions[indexQ].sub_questions[
                                            idxSubQ
                                        ].subQ_play_audio = 0;
                                    }
                                );
                            }
                        }
                    }
                });
            });
        },
        playAudioMatchRow1: function (sectionId, questionId, indexMatch) {
            var self = this;
            var contentTextBMatch =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).shuffle_row_1[indexMatch]
                    .content_text_a;
            var checkPlay =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).shuffle_row_1[indexMatch]
                    .play_audio;

            self.pauseAllAudio();
            if (checkPlay === 0) {
                let utterance = self.speechText(contentTextBMatch);
                utterance.onend = (event) => {
                    const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].shuffle_row_1[
                        indexMatch
                    ].play_audio = 0;
                };
                const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].shuffle_row_1[
                    indexMatch
                ].play_audio = 1;
            }
        },
        playAudioMatchRow2: function (sectionId, questionId, indexMatch) {
            var self = this;
            var audioLinkMatch =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).shuffle_row_2[indexMatch]
                    .audio_url;
            var contentTextBMatch =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).shuffle_row_2[indexMatch]
                    .content_text_b;
            var checkPlay =
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).shuffle_row_2[indexMatch]
                    .play_audio;
            // if (audioLinkMatch) {

            self.pauseAllAudio();
            var audio = document.getElementById(
                "row2_audio_match" +
                    (questionId + 1) +
                    "_" +
                    (indexMatch + 1)
            );
            if (checkPlay === 0) {
                if (audioLinkMatch) {
                    audio.load();
                    audio.play();
                } else if (contentTextBMatch) {
                    let voices = speechSynthesis.getVoices();
                    let utterance = self.speechText(contentTextBMatch);
                    utterance.onend = (event) => {
                        const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].shuffle_row_2[
                            indexMatch
                        ].play_audio = 0;
                    };
                }
                const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].shuffle_row_2[
                    indexMatch
                ].play_audio = 1;
            }
        },
        speechText: function (text, lang = "en-US", rate = 0.8) {
            let utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = lang;
            utterance.rate = rate;
            speechSynthesis.speak(utterance);
            speechSynthesis.getVoices();

            return utterance;
        },
        startTimer: function (duration) {
            var self = this;
            console.log("duration: ", duration);

            var timer = duration,
                minutes,
                seconds;
            self.intervalIdStartTimer = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                $("#time").text(minutes + ":" + seconds);

                if (--timer < 0) {
                    $(".modal-time-out").modal("show");
                    clearInterval(self.intervalIdStartTimer);
                }
            }, 1000);
        },
        clickAnswer: function (
            isResult,
            questionId,
            idxSubQuestion,
            idxAnswer,
            numberCorrectAns = 1,
            sectionId
        ) {
            var self = this;
            if (isResult) {
                return;
            }
            const selector = `.q${questionId}_edit-step${
                idxSubQuestion + 1
            }_answer${idxAnswer + 1}`;
            const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId);
            const currentIdxQuestion = self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId);
            if ($(selector).hasClass("choose_answer")) {
                $(selector).removeClass("choose_answer");
                $(selector).children().removeClass("color_white");
                $(selector).children().children().removeClass("color_white");
                if(numberCorrectAns > 1) {
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[idxSubQuestion].countChoose -= 1
                    if(self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[idxSubQuestion].countChoose < numberCorrectAns){
                        $( '.qna_answer_'+sectionId+'_'+questionId + '_sub_q' + idxSubQuestion ).each(function() {
                            if (!$(this).hasClass("choose_answer")) {
                                $(this).removeClass("disable_no_choose");
                                $(this).children().removeClass("color_white");
                                $(this).children().children().removeClass("color_white");
                            }
                        });
                    }
                }
            } else if(numberCorrectAns == 1 || self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[idxSubQuestion].countChoose < numberCorrectAns){
                if(numberCorrectAns == 1){
                    $( '.qna_answer_' + sectionId + '_'+questionId + '_sub_q' + idxSubQuestion ).each(function() {
                        $(this).removeClass("choose_answer");
                        $(this).children().removeClass("color_white");
                        $(this).children().children().removeClass("color_white");

                    });
                }

                $(selector).addClass("choose_answer");
                $(selector).children().addClass("color_white");
                $(selector).children().children().addClass("color_white");
                console.log(numberCorrectAns)
                console.log(self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[idxSubQuestion].countChoose)
                if(numberCorrectAns > 1) {
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[idxSubQuestion].countChoose += 1
                    if (self.items.sections[currentIdxSection].questions[currentIdxQuestion].sub_questions[idxSubQuestion].countChoose == numberCorrectAns) {
                        $( '.qna_answer_'+sectionId+'_'+questionId + '_sub_q' + idxSubQuestion ).each(function() {
                            if (!$(this).hasClass("choose_answer")) {
                                $(this).removeClass("choose_answer");
                                $(this).addClass("disable_no_choose");
                                $(this).children().addClass("color_white");
                                $(this).children().children().addClass("color_white");
                            }
                        });
                    }
                }
            }
        },
        setIdxEditStepForQAQuestion: function (sectionId, questionId, idxSubQuestion) {
            console.log(">>> setIdxEditStepForQAQuestion");
            var self = this;
            const qQuestionId = `q${questionId}`;
            let indexEditStep = idxSubQuestion + 1;
            self.arrIdxStepQA[qQuestionId] = indexEditStep;

            $(`.q${questionId}-btn-pre-qa`).show();
            $(`.q${questionId}-btn-next-qa`).show();
            if (indexEditStep == 1) {
                $(`.q${questionId}-btn-pre-qa`).hide();
            } else if (
                indexEditStep ==
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).sub_questions.length
            ) {
                $(`.q${questionId}-btn-next-qa`).hide();
            }

            $(`.${qQuestionId}_collapse`).removeClass("show");
            $(`.${qQuestionId}_btn-step-progress`).removeClass("style_active");
            $(`#${qQuestionId}_edit-step${indexEditStep}`).addClass("show");
            $(
                `.${qQuestionId}_btn-step-progress:eq(${indexEditStep - 1})`
            ).addClass("style_active");
        },
        preQA: function (sectionId, questionId) {
            var self = this;
            const qQuestionId = `q${questionId}`;
            console.log(">>> qQuestionId: ", qQuestionId);
            let indexEditStep = 1;
            if (self.arrIdxStepQA[qQuestionId]) {
                indexEditStep = self.arrIdxStepQA[qQuestionId];
            } else {
                self.arrIdxStepQA[qQuestionId] = indexEditStep;
            }

            if (indexEditStep > 1) {
                $(`.q${questionId}-btn-next-qa`).show();
                if (indexEditStep == 2) {
                    $(`.q${questionId}-btn-pre-qa`).hide();
                }

                $(`.${qQuestionId}_collapse`).removeClass("show");
                $(`.${qQuestionId}_btn-step-progress`).removeClass(
                    "style_active"
                );

                indexEditStep--;
                self.arrIdxStepQA[qQuestionId] = indexEditStep;

                $(`#${qQuestionId}_edit-step${indexEditStep}`).addClass("show");
                $(
                    `.${qQuestionId}_btn-step-progress:eq(${indexEditStep - 1})`
                ).addClass("style_active");
            }
        },
        nextQA: function (sectionId, questionId) {
            var self = this;
            const qQuestionId = `q${questionId}`;
            console.log(">>> qQuestionId: ", qQuestionId);
            let indexEditStep = 1;
            if (self.arrIdxStepQA[qQuestionId]) {
                indexEditStep = self.arrIdxStepQA[qQuestionId];
            } else {
                self.arrIdxStepQA[qQuestionId] = indexEditStep;
            }
            console.log(">>> indexEditStep: ", indexEditStep);

            if (
                indexEditStep <
                self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).sub_questions.length
            ) {
                $(`.q${questionId}-btn-pre-qa`).show();
                if (
                    indexEditStep ==
                    self.items.sections.find(x => x.section_id == sectionId).questions.find(x => x.question_id == questionId).sub_questions.length - 1
                ) {
                    $(`.q${questionId}-btn-next-qa`).hide();
                }

                $(`.${qQuestionId}_collapse`).removeClass("show");
                $(`.${qQuestionId}_btn-step-progress`).removeClass(
                    "style_active"
                );

                indexEditStep++;
                self.arrIdxStepQA[qQuestionId] = indexEditStep;

                $(`#${qQuestionId}_edit-step${indexEditStep}`).addClass("show");
                $(
                    `.${qQuestionId}_btn-step-progress:eq(${indexEditStep - 1})`
                ).addClass("style_active");
            }
        },
        preSection: function () {
            var self = this;
            let currentIdxSection = self.currentIdxSection;
            let indexEditStep = currentIdxSection - 1;

            if (indexEditStep >= 0) {
                $(`.s-btn-next-section`).show();
                if (indexEditStep == 0) {
                    $(`.s-btn-pre-section`).hide();
                }

                $(`.s_collapse`).removeClass("show");

                self.currentIdxSection = indexEditStep;

                $(`#s${indexEditStep}_edit-step`).addClass("show");
            }
        },
        nextSection: function () {
            var self = this;
            let currentIdxSection = self.currentIdxSection;
            let indexEditStep = currentIdxSection + 1;
            console.log(">>> indexEditStep: ", indexEditStep);

            if (indexEditStep < self.items.sections.length) {
                $(`.s-btn-pre-section`).show();
                if (indexEditStep == self.items.sections.length - 1) {
                    $(`.s-btn-next-section`).hide();
                }

                $(`.s_collapse`).removeClass("show");

                self.currentIdxSection = indexEditStep;

                $(`#s${indexEditStep}_edit-step`).addClass("show");
            }
        },
        getCookie: function (cname) {
            var name = cname + "=";
            console.log(">>>> document.cookie: ", document.cookie);
            var decodedCookie = decodeURIComponent(document.cookie);
            console.log(">>>> decodedCookie: ", decodedCookie);
            var ca = decodedCookie.split(";");
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == " ") {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        },
        shuffleArray: function (array) {
            let currentIndex = array.length,
                randomIndex;

            // While there remain elements to shuffle.
            while (currentIndex != 0) {
                // Pick a remaining element.
                randomIndex = Math.floor(Math.random() * currentIndex);
                currentIndex--;

                // And swap it with the current element.
                [array[currentIndex], array[randomIndex]] = [
                    array[randomIndex],
                    array[currentIndex],
                ];
            }

            return array;
        },
        getUrlVars: function () {
            var vars = [],
                hash;
            var hashes = window.location.href
                .slice(window.location.href.indexOf("?") + 1)
                .split("&");
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split("=");
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        },
        convertData: function () {
            var self = this;
            let data = [];

            if (self.items?.topic_test_type === TOPIC_TEST_TYPE.IELTS_WRITING) {
                self.items.sections.forEach((section, idxSection) => {
                    section.questions.forEach((elQ, idxQ) => {
                        const answer = $(`#question-${elQ.question_id}-answer`).val();
                        data.push({
                            question_id: elQ.question_id,
                            answer: answer,
                        });
                    });
                });
            } else {
                self.items.sections.forEach(section => {
                    section.questions.forEach((elQ, idxQ) => {
                        let answer = [];
                        switch (elQ.main_type) {
                            case 1:
                                elQ.correct_answer.forEach((el, idx) => {
                                    const txt = $(
                                        `#question-${elQ.question_id}-drag${
                                            idx + 1
                                        }_answer`
                                    )
                                        .text()
                                        .trim();

                                    answer.push(txt);
                                });

                                data.push({
                                    question_id: elQ.question_id,
                                    answer: answer,
                                });
                                break;

                            case 2:
                                elQ.sub_questions.forEach((el, idx) => {
                                    let arrVal = [];
                                    const objChooseAnswer = $(
                                        `.q${elQ.question_id}_edit-step${
                                            idx + 1
                                        }_answer.choose_answer`
                                    );
                                    console.log('>>> objChooseAnswer: ', objChooseAnswer);
                                    objChooseAnswer.each(function (i) {
                                        console.log(i);
                                        const val = $(
                                            `.q${elQ.question_id}_edit-step${
                                                idx + 1
                                            }_answer.choose_answer:eq(${i})`
                                        ).children()
                                        .last()
                                        .text()
                                        .trim();

                                        arrVal.push(val);
                                    });
                                    answer.push(arrVal);
                                });

                                data.push({
                                    question_id: elQ.question_id,
                                    answer: answer,
                                });
                                break;

                            case 3:
                                let idxRow = 1;
                                let arrRow = elQ.row_1;
                                if (elQ.row_2.length <= elQ.row_1.length) {
                                    arrRow = elQ.row_2;
                                    idxRow = 2;
                                }

                                arrRow.forEach((el, idx) => {
                                    const questionEndpoint =
                                        self.questionEndpoints[
                                            `q${elQ.question_id}-row${idxRow}_matching${
                                                idx + 1
                                            }`
                                        ];
                                    if (questionEndpoint?.connections.length > 0) {
                                        let sourceVal =
                                            questionEndpoint?.connections[0].source
                                                .childNodes[0].textContent;
                                        // let sourceVal =
                                        //     questionEndpoint?.connections[0].source
                                        //         .childNodes[0].src;
                                        if (!sourceVal) {
                                            sourceVal = self.getSrcLinkForMatching(
                                                questionEndpoint?.connections[0]
                                                    .source.innerHTML
                                            );
                                        }
                                        let targetVal =
                                            questionEndpoint?.connections[0].target
                                                .childNodes[0].textContent;
                                        if (!targetVal) {
                                            // targetVal =
                                            //     questionEndpoint?.connections[0].target
                                            //         .childNodes[0].src;
                                            targetVal = self.getSrcLinkForMatching(
                                                questionEndpoint?.connections[0]
                                                    .target.innerHTML
                                            );
                                        }
                                        // if (!targetVal) {
                                        //     targetVal =
                                        //         questionEndpoint?.connections[0].target
                                        //             .childNodes[0].childNodes[0].src;
                                        // }
                                        answer.push([sourceVal, targetVal]);
                                    }
                                });

                                data.push({
                                    question_id: elQ.question_id,
                                    answer: answer,
                                });
                                break;

                            case 4:
                                elQ.arr_content_main.forEach((el, idx) => {
                                    const arrInputVal = [];
                                    const lengthQuestionMark = (
                                        el.match(/\?\?/g) || []
                                    ).length;
                                    for (
                                        let index = 0;
                                        index < lengthQuestionMark;
                                        index++
                                    ) {
                                        const val = $(
                                            `#question-${elQ.question_id}_content-${
                                                idx + 1
                                            }_input-${index + 1}`
                                        )
                                            .text()
                                            .trim();
                                        arrInputVal.push(val.split(" "));
                                    }
                                    answer.push(arrInputVal);
                                });
                                data.push({
                                    question_id: elQ.question_id,
                                    answer: answer,
                                });
                                break;
                            case 5:
                                elQ.arr_content_main.forEach((el, idx) => {
                                    const arrInputVal = [];
                                    const lengthQuestionMark = (
                                        el.match(/\?\?/g) || []
                                    ).length;
                                    for (
                                        let index = 0;
                                        index < lengthQuestionMark;
                                        index++
                                    ) {
                                        const val = $(
                                            `#question-${elQ.question_id}_content-${
                                                idx + 1
                                            }_input-${index + 1}`
                                        )
                                            .val()
                                            .trim();
                                        arrInputVal.push(val.split(" "));
                                    }
                                    answer.push(arrInputVal);
                                });
                                data.push({
                                    question_id: elQ.question_id,
                                    answer: answer,
                                });
                                break;
                        }
                    });
                });
            }

            return data;
        },
        getSrcLinkForMatching: function (strHtml) {
            let val = null;
            let firstSrc = strHtml.slice(
                strHtml.indexOf("src=") + 5,
                strHtml.length
            );
            if (firstSrc != -1) {
                val = firstSrc.slice(0, firstSrc.indexOf('"'));
            }

            return val;
        },
        sourceClickHandler: function (isResult, idQuestion, idTag, idxContent) {
            if (isResult) {
                return;
            }

            var self = this;
            var target = `#${idTag}`;

            if (self.questionEndpoints[$(target).attr("id")]) {
                const connections =
                    self.questionEndpoints[$(target).attr("id")].connections;
                if (connections.length > 0) {
                    console.log(">>>>>>>>>>>>>>>>>>> deleteEndpoint <<<<<<<<<");
                    const connectionSourceId = connections[0].sourceId;
                    const connectionTargetId = connections[0].targetId;
                    jsPlumb.deleteEndpoint(
                        self.questionEndpoints[connectionSourceId]
                    );
                    jsPlumb.deleteEndpoint(
                        self.questionEndpoints[connectionTargetId]
                    );
                }
            }

            $(".match-choose").removeClass("item-match-active");
            $(target).addClass("item-match-active");
            console.log(">>> sourceClickHandler, idQuestion: ", idQuestion);
            console.log(
                ">>> sourceClickHandler, self.idTarget: ",
                self.idTarget
            );
            self.idSource = idQuestion;
            self.sourceTagId = idTag;
            self.idxContentSource = idxContent;
            if (self.idTarget == idQuestion) {
                jsPlumb.importDefaults({
                    ConnectionsDetachable: true,
                    ReattachConnections: true,
                    maxConnections: 1,
                    Container: "trial_test",
                });

                if (!self.arrFirstMatching[idQuestion]) {
                    self.handleSourceAndTargetEndPoint(
                        target,
                        `#${self.targetTagId}`
                    );
                    self.arrFirstMatching = [];
                    self.arrFirstMatching[idQuestion] = true;
                } else {
                    self.handleSourceEndpoint(target, idxContent);
                    self.arrFirstMatching = [];
                    self.arrFirstMatching[idQuestion] = true;
                }
            }
        },
        targetEndpoint: function (isResult, idQuestion, idTag) {
            if (isResult) {
                return;
            }

            var self = this;
            var target = `#${idTag}`;

            if (self.questionEndpoints[$(target).attr("id")]) {
                const connections =
                    self.questionEndpoints[$(target).attr("id")].connections;
                if (connections.length > 0) {
                    console.log(">>>>>>>>>>>>>>>>>>> deleteEndpoint <<<<<<<<<");
                    const connectionSourceId = connections[0].sourceId;
                    const connectionTargetId = connections[0].targetId;
                    jsPlumb.deleteEndpoint(
                        self.questionEndpoints[connectionSourceId]
                    );
                    jsPlumb.deleteEndpoint(
                        self.questionEndpoints[connectionTargetId]
                    );
                }
            }

            $(".match-choose").removeClass("item-match-active");
            $(target).addClass("item-match-active");

            console.log(">>> targetEndpoint, idQuestion: ", idQuestion);
            console.log(">>> targetEndpoint, self.idSource: ", self.idSource);
            self.idTarget = idQuestion;
            self.targetTagId = idTag;
            if (self.idSource == idQuestion) {
                jsPlumb.importDefaults({
                    ConnectionsDetachable: true,
                    ReattachConnections: true,
                    maxConnections: 1,
                    Container: "trial_test",
                });

                if (!self.arrFirstMatching[idQuestion]) {
                    self.handleSourceAndTargetEndPoint(
                        `#${self.sourceTagId}`,
                        target
                    );
                    self.arrFirstMatching = [];
                    self.arrFirstMatching[idQuestion] = true;
                } else {
                    self.handleTargetEndpoint(target);
                    self.arrFirstMatching = [];
                    self.arrFirstMatching[idQuestion] = true;
                }
            }
        },
        handleSourceAndTargetEndPoint: function (
            selectorSource,
            selectorTarget
        ) {
            var self = this;

            // if (self.questionEndpoints[$(selectorSource).attr("id")]) {
            //     const connections = self.questionEndpoints[$(selectorSource).attr("id")].connections;
            //     if (connections.length > 0) {
            //         console.log(">>>>>>>>>>>>>>>>>>> deleteEndpoint <<<<<<<<<");
            //         const connectionSourceId = connections[0].sourceId;
            //         const connectionTargetId = connections[0].targetId;
            //         jsPlumb.deleteEndpoint(
            //             self.questionEndpoints[connectionSourceId]
            //         );
            //         jsPlumb.deleteEndpoint(
            //             self.questionEndpoints[connectionTargetId]
            //         );
            //     }
            // }

            // add a new one on the clicked element:
            self.questionEndpoints[$(selectorSource).attr("id")] =
                jsPlumb.addEndpoint($(selectorSource), sourceOption);
            connectObj.source =
                self.questionEndpoints[$(selectorSource).attr("id")];

            self.questionEndpoints[$(selectorTarget).attr("id")] =
                jsPlumb.addEndpoint($(selectorTarget), targetOption);
            connectObj.target =
                self.questionEndpoints[$(selectorTarget).attr("id")];

            self.connectEndpoints(connectObj, self.idxContentSource);
        },
        handleSourceEndpoint: function (target, idxContent) {
            var self = this;
            //remove existing start endpoint, if any:
            // jsPlumb.deleteEndpoint(
            //     self.questionEndpoints[$(target).attr("id")]
            // );
            console.log(">>> self.questionEndpoints: ", self.questionEndpoints);
            // if (self.questionEndpoints[$(target).attr("id")]) {
            //     const connections = self.questionEndpoints[$(target).attr("id")].connections;
            //     if (connections.length > 0) {
            //         console.log(">>>>>>>>>>>>>>>>>>> deleteEndpoint <<<<<<<<<");
            //         const connectionSourceId = connections[0].sourceId;
            //         const connectionTargetId = connections[0].targetId;
            //         jsPlumb.deleteEndpoint(
            //             self.questionEndpoints[connectionSourceId]
            //         );
            //         jsPlumb.deleteEndpoint(
            //             self.questionEndpoints[connectionTargetId]
            //         );
            //     }
            // }

            // add a new one on the clicked element:
            self.questionEndpoints[$(target).attr("id")] = jsPlumb.addEndpoint(
                $(target),
                sourceOption
            );
            connectObj.source = self.questionEndpoints[$(target).attr("id")];
            self.connectEndpoints(connectObj, idxContent);
            console.log(
                "source, self.questionEndpoints: ",
                self.questionEndpoints
            );
        },
        handleTargetEndpoint: function (target) {
            var self = this;
            //if (!questionEndpoints[0]) return; // don't respond if a source hasn't been selected
            // remove existing endpoint if any
            // jsPlumb.deleteEndpoint(
            //     self.questionEndpoints[$(target).attr("id")]
            // );
            console.log(">>> self.questionEndpoints: ", self.questionEndpoints);
            // if (self.questionEndpoints[$(target).attr("id")]) {
            //     const connections = self.questionEndpoints[$(target).attr("id")].connections;
            //     if (connections.length > 0) {
            //         console.log(">>>>>>>>>>>>>>>>>>> deleteEndpoint <<<<<<<<<");
            //         const connectionSourceId = connections[0].sourceId;
            //         const connectionTargetId = connections[0].targetId;
            //         jsPlumb.deleteEndpoint(
            //             self.questionEndpoints[connectionSourceId]
            //         );
            //         jsPlumb.deleteEndpoint(
            //             self.questionEndpoints[connectionTargetId]
            //         );
            //     }
            // }

            console.log($(target).attr("id"));
            //create a new one:
            self.questionEndpoints[$(target).attr("id")] = jsPlumb.addEndpoint(
                $(target),
                targetOption
            );
            connectObj.target = self.questionEndpoints[$(target).attr("id")];
            self.connectEndpoints(connectObj, null);
            console.log(
                "target, self.questionEndpoints: ",
                self.questionEndpoints
            );
        },
        connectEndpoints: function (connectObj, idxContent) {
            var self = this;
            console.log(">>> idxContent: ", idxContent);

            let connect = { ...connectObj };
            console.log(">>> self.paintStyle: ", self.paintStyle);

            if (self.paintStyle) {
                console.log(">>> ZOOOOOOOOOOOOO");
                connect = { ...connect, paintStyle: self.paintStyle };
            }

            if (idxContent != null) {
                var colorsArray = [
                    "#FF0000",
                    "#FF8C00",
                    "#008000",
                    "#0000FF",
                    "#BA55D3",
                ];
                console.log(
                    ">>> colorsArray idxContent: ",
                    colorsArray[idxContent]
                );
                self.paintStyle = {
                    stroke: colorsArray[idxContent],
                    strokeWidth: 5,
                };
                connect = {
                    ...connect,
                    paintStyle: {
                        stroke: colorsArray[idxContent],
                        strokeWidth: 5,
                    },
                };
            }
            console.log(">>> connect: ", connect);
            jsPlumb.connect(connect);
        },
        removeItemActiveHover: function () {
            $(".match-choose").removeClass("item-match-active");
        },
        IELTSWritingValidation: function () {
            console.log(">>> IELTSWritingValidation");
            var self = this;
            let isValid = true;

            if (self.items?.sections?.length == 0) {
                return isValid;
            }
            self.items?.sections.forEach((section, idxSection) => {
                section.questions.forEach((value, index) => {
                    console.log(value.question_id);
                    $(`#question-${value.question_id}-error_message`).text("");
                    const answer = $(`#question-${value.question_id}-answer`).val();
                    console.log(answer);
                    const matchValue = answer.match(/\S+/g);
                    console.log(matchValue);

                    if (matchValue != null) {
                        const words = matchValue.length;
                        if (words < value.word_minimum) {
                            isValid = false;
                            $(`#question-${value.question_id}-error_message`)
                                .addClass("error_msg")
                                .text(
                                    `Please enter at least ${value.word_minimum} words`
                                );
                        }
                    } else {
                        if (value.word_minimum >= 1) {
                            isValid = false;
                            $(`#question-${value.question_id}-error_message`)
                                .addClass("error_msg")
                                .text(
                                    `Please enter at least ${value.word_minimum} words`
                                );
                        }
                    }
                });
            })

            return isValid;
        },
        submit: function () {
            var self = this;

            // Validate IELTS writing
            if (self.items?.topic_test_type === TOPIC_TEST_TYPE.IELTS_WRITING) {
                if (!self.IELTSWritingValidation()) {
                    return;
                }
            }

            const data = self.convertData();
            console.log(data);
            // Submit
            $("body").LoadingOverlay("show");
            let url = "/student/save-test-results";
            if (self.testType == TEST_TYPE.STAFF_PRE_TEST) {
                url = "/student/save-results-pre-test";
            }

            // const trialTestToken = localStorage.getItem("trial_test_token");
            axios.defaults.baseURL = baseApiUrl;
            // axios.defaults.headers.common.authorization = trialTestToken;
            axios
                .post(url, {
                    test_code: self.testCode,
                    // id_booking: self.idBooking,
                    data: data,
                    id_student_test_result: self.items.id_student_test_result,
                    test_type: self.testType,
                })
                .then(function (response) {
                    console.log(response);
                    if (response.data.code === "10000") {
                        // window.location.href = `/student/trial-test?id=${response.data.test_result_id}`;
                        self.testResultId = response.data.test_result_id;
                        self.testCode = response.data.test_code;
                        self.scoreScaleReceived =
                            response.data.score_scale_received;
                        self.resultType = response.data.result_type;
                        self.testType = response.data.test_type;
                        self.resultTestIelts = response.data.result_test_ielts;
                        clearInterval(self.intervalIdStartTimer);
                        $(".modal-test-results").modal("show");
                    } else {
                        if (response.data.message) {
                            alert(response.data.message);
                        } else {
                            alert("Error");
                        }
                    }
                })
                .catch(function (error) {
                    console.log(error);
                    if (error?.response?.data?.message) {
                        alert(error?.response?.data?.message);
                    } else {
                        alert("Error");
                    }
                })
                .then(function () {
                    // always executed
                    $("body").LoadingOverlay("hide");
                });
        },
        enderAudio: function (sectionId, questionId) {
            var self = this;
            const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].play_audio = 0;
        },
        enderAudioTitle: function (sectionId, questionId) {
            var self = this;
            const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].title_play_audio_with_url = 0;
        },
        enderAudioMatch: function (sectionId, questionId, indexMatch) {
            var self = this;
            const currentIdxSection = self.items.sections.findIndex(x => x.section_id == sectionId)
                    const currentIdxQuestion =self.items.sections[currentIdxSection].questions.findIndex(x => x.question_id == questionId)
                    self.items.sections[currentIdxSection].questions[currentIdxQuestion].shuffle_row_2[
                indexMatch
            ].play_audio = 0;
        },
        showDetail: function () {
            var self = this;
            // $("body").LoadingOverlay("show");
            // window.location.href = `/student/trial-test?code=${
            //     self.testCode
            // }&type=result&test_type=${self.testType}&trial_test_token=${localStorage.getItem("trial_test_token")}`;
            window.location.href = `/student/trial-test?code=${self.testCode}&type=result&test_type=${self.testType}`;
        },
        reloadMatchingAfterSort: function () {
            console.log(">>> reloadMatchingAfterSort");
            var self = this;
            jsPlumb.importDefaults({
                ConnectionsDetachable: true,
                ReattachConnections: true,
                maxConnections: 1,
                Container: "trial_test",
            });
            const maxElOnRow = 5;
            let currentIdxElOnRow = 0;
            let arrIdIgnore = [];
            console.log(">>> self.questionEndpoints: ", self.questionEndpoints);
            const arrQuestionEndpoints = Object.keys(
                self.questionEndpoints
            ).map((key) => key);
            console.log(">>> arrQuestionEndpoints: ", arrQuestionEndpoints);
            arrQuestionEndpoints.forEach((element) => {
                const idx = element;
                const el = self.questionEndpoints[element];
                console.log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>> idx: ", idx);
                console.log(">>>>>>>>>>>>>>>>>>>>>>>>>>>>> el: ", el);
                if (!arrIdIgnore.includes(idx)) {
                    if (el.connections.length > 0) {
                        const connection = el.connections[0];
                        const idSource = connection.sourceId;
                        const idTarget = connection.targetId;
                        arrIdIgnore.push(idSource, idTarget);

                        jsPlumb.deleteEndpoint(
                            self.questionEndpoints[idSource]
                        );
                        jsPlumb.deleteEndpoint(
                            self.questionEndpoints[idTarget]
                        );

                        self.questionEndpoints[idSource] = jsPlumb.addEndpoint(
                            $(`#${idSource}`),
                            sourceOption
                        );
                        self.questionEndpoints[idTarget] = jsPlumb.addEndpoint(
                            $(`#${idTarget}`),
                            targetOption
                        );

                        const connectObj = {
                            source: null,
                            target: null,
                        };
                        connectObj.source = self.questionEndpoints[idSource];
                        connectObj.target = self.questionEndpoints[idTarget];

                        if (currentIdxElOnRow >= maxElOnRow) {
                            currentIdxElOnRow = 0;
                        }
                        console.log(
                            ">>>>>>>>>>>>>>>>>>> connection <<<<<<<<<<<<<<"
                        );
                        self.connectEndpoints(connectObj, currentIdxElOnRow);
                        currentIdxElOnRow++;
                    }
                }
            });
        },
        showMatchingResult: function (questionId) {
            var self = this;
            self.isShowMatchingResult = true;
            console.log(
                "questionId: ",
                $(`.btn-show-matching-result-q${questionId}`).text()
            );
            if (
                $(`.btn-show-matching-result-q${questionId}`)
                    .text()
                    .toLowerCase() == "xem trả lời"
            ) {
                $(`.btn-show-matching-result-q${questionId}`).text(
                    "Xem đáp án"
                );
                $(`.q${questionId}-icon-result-matching`).show();
                const index = self.arrIdxQShowResult.indexOf(questionId);
                if (index > -1) {
                    self.arrIdxQShowResult.splice(index, 1);
                }
            } else {
                $(`.btn-show-matching-result-q${questionId}`).text(
                    "Xem trả lời"
                );
                $(`.q${questionId}-icon-result-matching`).hide();
                self.arrIdxQShowResult.push(questionId);
            }

            jsPlumb.importDefaults({
                ConnectionsDetachable: true,
                ReattachConnections: true,
                maxConnections: 1,
                Container: "trial_test",
            });
            console.log(
                ">>>> self.questionEndpoints: ",
                self.questionEndpoints
            );
            // self.questionEndpoints = [];
            jsPlumb.reset();
            self.items?.sections.forEach((section, idxSection) => {
                section.questions.forEach((elQ, idxQ) => {
                    if (
                        elQ?.main_type == 3 &&
                        self.arrIdxQShowResult.includes(elQ.question_id)
                    ) {
                        console.log(">>>>>>>>>>>>>>>>>>>>>> case dap an");
                        const row1 = elQ.row_1;
                        const row2 = elQ.row_2;
                        console.log("row1: ", row1);
                        console.log("row2: ", row2);
                        row1.forEach((el, idx) => {
                            if (idx >= row1.length || idx >= row2.length) {
                                return;
                            }

                            let idSource = null;
                            let idTarget = null;

                            if (el.picture_url_a) {
                                idSource = $(`#question-${elQ.question_id}`)
                                    .find(`img[src='${el.picture_url_a}']`)[0]
                                    .parentNode.getAttribute("id");
                            } else if (el.content_text_a) {
                                idSource = $(`#question-${elQ.question_id}`)
                                    .find(
                                        `div[drag-content='${self.replaceAllSpecialChar(
                                            el.content_text_a
                                        )}']`
                                    )[0]
                                    .getAttribute("id");
                            }
                            if (
                                row2[idx].audio_url &&
                                !row2[idx].content_text_b &&
                                !row2[idx].picture_url_b
                            ) {
                                idTarget = $(`#question-${elQ.question_id}`)
                                    .find(
                                        `div[drag-content='${self.replaceAllSpecialChar(
                                            row2[idx].audio_url
                                        )}']`
                                    )[0]
                                    .getAttribute("id");
                            } else if (row2[idx].content_text_b) {
                                idTarget = $(`#question-${elQ.question_id}`)
                                    .find(
                                        `div[drag-content='${self.replaceAllSpecialChar(
                                            row2[idx].content_text_b
                                        )}']`
                                    )[0]
                                    .getAttribute("id");
                            } else if (row2[idx].picture_url_b) {
                                idTarget = $(`#question-${elQ.question_id}`)
                                    .find(
                                        `img[src='${row2[idx].picture_url_b}']`
                                    )[0]
                                    .parentNode.getAttribute("id");
                            }
                            console.log("idSource: ", idSource);
                            console.log("idTarget: ", idTarget);
                            // jsPlumb.deleteEndpoint(
                            //     self.questionEndpoints[idSource]
                            // );
                            // jsPlumb.deleteEndpoint(
                            //     self.questionEndpoints[idTarget]
                            // );

                            self.questionEndpoints[idSource] = jsPlumb.addEndpoint(
                                $(`#${idSource}`),
                                sourceOption
                            );
                            self.questionEndpoints[idTarget] = jsPlumb.addEndpoint(
                                $(`#${idTarget}`),
                                targetOption
                            );

                            const initConnectObj = {
                                source: null,
                                target: null,
                            };
                            initConnectObj.source =
                                self.questionEndpoints[idSource];
                            initConnectObj.target =
                                self.questionEndpoints[idTarget];
                            console.log("initConnectObj: ", initConnectObj);
                            self.connectEndpoints(initConnectObj, idx);
                        });
                    } else if (
                        elQ?.main_type == 3 &&
                        !self.arrIdxQShowResult.includes(elQ.question_id)
                    ) {
                        console.log(">>>>>>>>>>>>>>>>>>>>>> case tra loi");
                        console.log(">>> elQ?.question_id: ", elQ?.question_id);
                        elQ?.answer.forEach((element, idx) => {
                            let idSource = null;
                            let idTarget = null;

                            if (self.isUrlValid(element[0])) {
                                idSource = $(`#question-${elQ.question_id}`)
                                    .find(`img[src='${element[0]}']`)[0]
                                    .parentNode.getAttribute("id");
                            } else {
                                idSource = $(`#question-${elQ.question_id}`)
                                    .find(
                                        `div[drag-content='${self.replaceAllSpecialChar(
                                            element[0]
                                        )}']`
                                    )[0]
                                    .getAttribute("id");
                            }
                            if (self.isUrlValid(element[1])) {
                                const foundImg = $(`#question-${elQ.question_id}`).find(
                                    `img[src='${element[1]}']`
                                );

                                if (foundImg.length > 0) {
                                    idTarget = $(`#question-${elQ.question_id}`)
                                        .find(`img[src='${element[1]}']`)[0]
                                        .parentNode.getAttribute("id");
                                } else {
                                    idTarget = $(`#question-${elQ.question_id}`)
                                        .find(`source[src='${element[1]}']`)[0]
                                        .parentNode.parentNode.getAttribute("id");
                                }
                            } else {
                                idTarget = $(`#question-${elQ.question_id}`)
                                    .find(
                                        `div[drag-content='${self.replaceAllSpecialChar(
                                            element[1]
                                        )}']`
                                    )[0]
                                    .getAttribute("id");
                            }

                            // jsPlumb.deleteEndpoint(
                            //     self.questionEndpoints[idSource]
                            // );
                            // jsPlumb.deleteEndpoint(
                            //     self.questionEndpoints[idTarget]
                            // );

                            self.questionEndpoints[idSource] = jsPlumb.addEndpoint(
                                $(`#${idSource}`),
                                sourceOption
                            );
                            self.questionEndpoints[idTarget] = jsPlumb.addEndpoint(
                                $(`#${idTarget}`),
                                targetOption
                            );
                            const initConnectObj = {
                                source: null,
                                target: null,
                            };
                            initConnectObj.source =
                                self.questionEndpoints[idSource];
                            initConnectObj.target =
                                self.questionEndpoints[idTarget];
                            console.log("initConnectObj: ", initConnectObj);
                            self.connectEndpoints(initConnectObj, idx);
                        });
                    }
                });
            })
        },
        replaceAllSpecialChar: function (text) {
            if (!text) {
                return text;
            }

            return text.trim().replace(/\'/g, "_");
        },
        loadMatchingResult: function () {
            var self = this;
            if (self.isShowMatchingResult) {
                return;
            }

            console.log(">>> loadMatchingResult");

            jsPlumb.importDefaults({
                ConnectionsDetachable: true,
                ReattachConnections: true,
                maxConnections: 1,
                Container: "trial_test",
            });
            jsPlumb.reset();
            self.items?.sections.forEach((section, idxSection) => {
                section.questions.forEach((elQ, idxQ) => {
                    if (elQ?.main_type == 3) {
                        console.log(">>> elQ?.question_id: ", elQ?.question_id);
                        if (self.items?.type == "preview") {
                            const row1 = elQ.row_1;
                            const row2 = elQ.row_2;
                            console.log("row1: ", row1);
                            console.log("row2: ", row2);
                            row1.forEach((el, idx) => {
                                if (idx >= row1.length || idx >= row2.length) {
                                    return;
                                }

                                let idSource = null;
                                let idTarget = null;

                                if (el.picture_url_a) {
                                    idSource = $(`#question-${elQ.question_id}`)
                                        .find(`img[src='${el.picture_url_a}']`)[0]
                                        .parentNode.getAttribute("id");
                                } else if (el.content_text_a) {
                                    idSource = $(`#question-${elQ.question_id}`)
                                        .find(
                                            `div[drag-content='${self.replaceAllSpecialChar(
                                                el.content_text_a
                                            )}']`
                                        )[0]
                                        .getAttribute("id");
                                }
                                if (
                                    row2[idx].audio_url &&
                                    !row2[idx].content_text_b &&
                                    !row2[idx].picture_url_b
                                ) {
                                    idTarget = $(`#question-${elQ.question_id}`)
                                        .find(
                                            `div[drag-content='${self.replaceAllSpecialChar(
                                                row2[idx].audio_url
                                            )}']`
                                        )[0]
                                        .getAttribute("id");
                                } else if (row2[idx].content_text_b) {
                                    idTarget = $(`#question-${elQ.question_id}`)
                                        .find(
                                            `div[drag-content='${self.replaceAllSpecialChar(
                                                row2[idx].content_text_b
                                            )}']`
                                        )[0]
                                        .getAttribute("id");
                                } else if (row2[idx].picture_url_b) {
                                    idTarget = $(`#question-${elQ.question_id}`)
                                        .find(
                                            `img[src='${row2[idx].picture_url_b}']`
                                        )[0]
                                        .parentNode.getAttribute("id");
                                }
                                console.log("idSource: ", idSource);
                                console.log("idTarget: ", idTarget);

                                self.questionEndpoints[idSource] =
                                    jsPlumb.addEndpoint(
                                        $(`#${idSource}`),
                                        sourceOption
                                    );
                                self.questionEndpoints[idTarget] =
                                    jsPlumb.addEndpoint(
                                        $(`#${idTarget}`),
                                        targetOption
                                    );

                                const initConnectObj = {
                                    source: null,
                                    target: null,
                                };
                                initConnectObj.source =
                                    self.questionEndpoints[idSource];
                                initConnectObj.target =
                                    self.questionEndpoints[idTarget];
                                console.log("initConnectObj: ", initConnectObj);
                                self.connectEndpoints(initConnectObj, idx);
                            });
                        } else {
                            elQ?.answer.forEach((element, idx) => {
                                let idSource = null;
                                let idTarget = null;

                                if (self.isUrlValid(element[0])) {
                                    idSource = $(`#question-${elQ.question_id}`)
                                        .find(`img[src='${element[0]}']`)[0]
                                        .parentNode.getAttribute("id");
                                } else {
                                    idSource = $(`#question-${elQ.question_id}`)
                                        .find(
                                            `div[drag-content='${self.replaceAllSpecialChar(
                                                element[0]
                                            )}']`
                                        )[0]
                                        .getAttribute("id");
                                }
                                if (self.isUrlValid(element[1])) {
                                    const foundImg = $(
                                        `#question-${elQ.question_id}`
                                    ).find(`img[src='${element[1]}']`);

                                    if (foundImg.length > 0) {
                                        idTarget = $(`#question-${elQ.question_id}`)
                                            .find(`img[src='${element[1]}']`)[0]
                                            .parentNode.getAttribute("id");
                                    } else {
                                        idTarget = $(`#question-${elQ.question_id}`)
                                            .find(`source[src='${element[1]}']`)[0]
                                            .parentNode.parentNode.getAttribute(
                                                "id"
                                            );
                                    }
                                } else {
                                    idTarget = $(`#question-${elQ.question_id}`)
                                        .find(
                                            `div[drag-content='${self.replaceAllSpecialChar(
                                                element[1]
                                            )}']`
                                        )[0]
                                        .getAttribute("id");
                                }

                                self.questionEndpoints[idSource] =
                                    jsPlumb.addEndpoint(
                                        $(`#${idSource}`),
                                        sourceOption
                                    );
                                self.questionEndpoints[idTarget] =
                                    jsPlumb.addEndpoint(
                                        $(`#${idTarget}`),
                                        targetOption
                                    );
                                const initConnectObj = {
                                    source: null,
                                    target: null,
                                };
                                initConnectObj.source =
                                    self.questionEndpoints[idSource];
                                initConnectObj.target =
                                    self.questionEndpoints[idTarget];
                                console.log("initConnectObj: ", initConnectObj);
                                self.connectEndpoints(initConnectObj, idx);
                            });
                        }
                    }
                });
            })
        },
        wordCountOfTheAnswer: function (questionId) {
            var self = this;
            const value = $(`#question-${questionId}-answer`).val();
            const words = self.wordCount(value);
            $(`#question-${questionId}-show_word_count`).text(words);
        },
        wordCount: function (value) {
            let words = 0;

            if (!value) {
                return words;
            }

            const matchValue = value.match(/\S+/g);
            if (matchValue != null) {
                words = matchValue.length;
            }

            return words;
        }
    },
    updated() {
        var self = this;

        // Drag
        const containers = document.querySelectorAll(".container_drag");
        console.log(">>> updated, containers: ", containers);
        containers.forEach((container) => {
            container.addEventListener("dragover", dragOver);
            container.addEventListener("drop", drop);
            container.addEventListener("dragstart", dragStart);
            container.addEventListener("dragend", dragEnd);
        });

        if (self.items?.isResult && !self.isShowMatchingResult) {
            const el = document.getElementById("trial_test");
            self.resizeObserver.unobserve(el);
            self.resizeObserver.observe(el);
        }
    }
});

function textToSpeechBySort(e, isResult) {
    console.log(">>> Class sort_text_to_speech, click");
    console.log(e.target);
    const sectionId = e.target.getAttribute("speaker-section-id");
    const questionId = e.target.getAttribute("speaker-question-id");
    const keyAnswer = e.target.getAttribute("speaker-key-answer");
    console.log(">>> questionId: ", questionId);
    console.log(">>> keyAnswer: ", keyAnswer);
    trial_test.playAnswerSortAudio(sectionId, questionId, keyAnswer, isResult);
}
