const SKILL_TYPE = {
    IELTS_LISTENING: "IELTS_LISTENING",
    IELTS_READING: "IELTS_READING",
    IELTS_WRITING: "IELTS_WRITING"
};

var ielts_skill_synthesis = new Vue({
    el: "#ielts_skill_synthesis",
    data: {
        items: null,
        ieltsSkillSynthesisCode: null,
    },
    created: function () {
        var self = this;
        self.initData();
    },
    methods: {
        initData: function () {
            var self = this;
            const vars = self.getUrlVars();
            console.log(vars);
            const code = vars["code"];
            self.ieltsSkillSynthesisCode = code;
            const url = "/student/link-ielts-skills";
            const params = {
                ielts_skill_synthesis_code: code,
            };
            axios.defaults.baseURL = baseApiUrl;

            $("body").LoadingOverlay("show");
            axios
                .get(url, {
                    params: params,
                })
                .then(function (response) {
                    console.log(response);
                    if (response.data.code === "10000") {
                        self.items = response.data.data;
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
        startTest: function (skillType) {
            var self = this;
            switch (skillType) {
                case SKILL_TYPE.IELTS_LISTENING:
                    if (self.items?.ielts_listening?.has_been_completed) {
                        alert("Test has been completed");
                        break;
                    }

                    setTimeout(() => {
                        window.open(
                            self.items?.ielts_listening?.test_url,
                            "_blank"
                        );
                    }, 500);

                    break;

                case SKILL_TYPE.IELTS_READING:
                    if (self.items?.ielts_reading?.has_been_completed) {
                        alert("Test has been completed");
                        break;
                    }

                    setTimeout(() => {
                        window.open(
                            self.items?.ielts_reading?.test_url,
                            "_blank"
                        );
                    }, 500);

                    break;

                case SKILL_TYPE.IELTS_WRITING:
                    if (self.items?.ielts_writing?.has_been_completed) {
                        alert("Test has been completed");
                        break;
                    }

                    setTimeout(() => {
                        window.open(
                            self.items?.ielts_writing?.test_url,
                            "_blank"
                        );
                    }, 500);

                    break;
            }
        },
        openTestResults: function (skillType) {
            var self = this;
            switch (skillType) {
                case SKILL_TYPE.IELTS_LISTENING:
                    setTimeout(() => {
                        window.open(
                            self.items?.ielts_listening?.result_url,
                            "_blank"
                        );
                    }, 500);

                    break;

                case SKILL_TYPE.IELTS_READING:
                    setTimeout(() => {
                        window.open(
                            self.items?.ielts_reading?.result_url,
                            "_blank"
                        );
                    }, 500);

                    break;

                case SKILL_TYPE.IELTS_WRITING:
                    setTimeout(() => {
                        window.open(
                            self.items?.ielts_writing?.result_url,
                            "_blank"
                        );
                    }, 500);

                    break;
            }
        },
    },
});
