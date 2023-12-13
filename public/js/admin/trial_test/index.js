listTag = listTag.split(",");
$(document).ready(function () {
    $("#input_tag").chosen({
        no_results_text: "Oops, nothing found!"
    })
    $("#input_tag").chosen().change(function(){
        app_list_topic_trial.input_tag = $("#input_tag").chosen().val();
    });
    $("#filter_tag").chosen({
        no_results_text: "Oops, nothing found!"
    })
    $("#filter_tag").chosen().change(function(){
        app_list_topic_trial.tag_filter = $("#filter_tag").chosen().val();
        app_list_topic_trial.actionFilter();
    });
});

const app_list_topic_trial = new Vue({
    el: '#list_trial_test',
    data: {
        topics: [],
        input_topic: '',
        input_tag: null,
        input_folder: 'Trial',
        input_test_type: 'COMMON',
        input_level: 1,
        input_time_test: 1,
        input_publish_status: 1,
        check_all: false,
        listCheckTopic: [],
        pagination: {
            total: 0,
            per_page: 20,
            from: 1,
            to: 0,
            current_page: 1,
            last_page: 1,
        },
        array_level: [
            {
              'title': 'KINDERGARTEN',
              'value': 1
            },
            {
                'title': 'KIDS',
                'value': 2
            },
            {
                'title': 'TEENS',
                'value': 3
            },
            {
                'title': 'ADULT',
                'value': 4
            },
        ],
        arr_publish_status: [
            {
                'title': 'Draft',
                'value': 1
            },
            {
                'title': 'Published',
                'value': 2
            }
        ],
        id_topic_edit: null,
        creator_oid: '',
        arrayCreator: [],
        action_type: 1,
        folder_filter: '',
        test_type_filter: '',
        status_filter: '',
        name_filter: '',
        tag_filter: '',
        source: '',
        list_tag: listTag || [],
    },
    components: {
    },
    async mounted() {
        var url_string = window.location.href;
        var url = new URL(url_string);
        var self = this;
        var auth_token = url.searchParams.get('auth_token');
        if(auth_token) {
            localStorage.setItem('trial_test_token', auth_token);
        }
        var source = url.searchParams.get('source');
        console.log(source);
        if(source) {
            localStorage.setItem('source', source);
            self.source = source;
        }
        await this. getAllCreator();
        await this.initData();
    },
    created(){
        $('#body_table_topic').prop('hidden', true);
    },
    computed: {
        isActived: function() {
            return this.pagination.current_page;
        },
        pagesNumber: function() {
            if (!this.pagination.to) {
                return [];
            }
            var from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            var to = from + this.offset * 2;
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
    },
    methods: {
        // getMe: function(){
        //     axios.defaults.baseURL = baseUrl;
        //     var auth_token = localStorage.getItem('trial_test_token');
        //     axios.defaults.headers.common.authorization = auth_token;
        //     var params = {};
        //     axios.get( '/admin/me', {
        //         params
        //     }).then((response) => {
        //         console.log(response.data.code);
        //         if(response.data.code === '10000'){
        //             console.log(2222);
        //             console.log(response.data.data.id);
        //             var admin_id = response.data.data.id;
        //             if(admin_id) localStorage.setItem('admin_id', admin_id);
        //
        //         }
        //     }).catch((errors) => {
        //         console.log(errors);
        //     });
        // },
        initData: function (){
            var self = this;
            axios.defaults.baseURL = baseUrl;
            const auth_token = localStorage.getItem('trial_test_token');
            axios.defaults.headers.common.authorization = auth_token;
            $('body').LoadingOverlay('show');
            axios.get( '/admin/trial-test/data-topics', {
                params: {
                    page: self.pagination.current_page,
                    creator_oid: self.creator_oid,
                    folder_filter: self.folder_filter,
                    test_type_filter: self.test_type_filter,
                    status_filter: self.status_filter,
                    name_filter: self.name_filter,
                    tags_filter: self.tag_filter,
                },
            }).then((response) => {
                  if(response.data.code === '10000'){
                      self.topics = response.data.data.data;
                      self.pagination.current_page = response.data.data.current_page;
                      self.pagination.from = response.data.data.from;
                      self.pagination.to = response.data.data.to;
                      self.pagination.last_page = response.data.data.last_page;
                      self.pagination.total = response.data.data.total;
                      self.pagination.per_page = response.data.data.per_page;
                      self.check_all = false;
                      $('.check_once_topic').each(function () {
                          $(this).prop('checked', false);
                      });
                  }
                  $('#body_table_topic').prop('hidden', false);
                  if(self.pagination.total > self.pagination.per_page) {
                        $('#pagination_topic').prop('hidden', false);
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
        getAllCreator: function (){
            var self = this;
            axios.defaults.baseURL = baseApiIspeak;
            var source = localStorage.getItem('source');
            console.log(source);
            if(source === 'safe_zone'){
                return;
            }
            var auth_token = localStorage.getItem('trial_test_token');
            axios.defaults.headers.common.authorization = auth_token;
            axios.get( '/admin/administrators/all?idDepartment=3').then((response) => {
                if(response.data.code === '10000'){
                    self.arrayCreator = [];
                    response.data.data.data.forEach(function(value){
                        var obj = {
                            id: value._id,
                            full_name: value.fullname,
                            user_name: value.username
                        }
                        self.arrayCreator.push(obj);
                    });
                    console.log(self.arrayCreator);
                }
            }).catch((errors) => {
            });
        },
        saveTopicTrialTest: function (){
            var self = this;
            var flagError = 0;
            if(!self.input_topic) {
                $('#input_topic_error').text('Topic is required')
                flagError = 1;
            }
            if(flagError == 1){
                return;
            }
            axios.defaults.baseURL = baseUrl;
            var auth_token = localStorage.getItem('trial_test_token');
            axios.defaults.headers.common.authorization = auth_token;
            $('body').LoadingOverlay('show');
            axios.post( '/admin/trial-test/save-topic', {
                id_topic: self.id_topic_edit,
                name_topic: self.input_topic,
                folder: self.input_folder,
                test_type: self.input_test_type,
                level: self.input_level,
                test_time: self.input_time_test,
                publish_status: self.input_publish_status,
                tags: self.input_tag,
            }).then((response) => {
                if(response.data.code === '10000'){
                    self.initData();
                    $('#modal_topic_trial_test').modal('hide');
                }else{
                    $('#input_topic_error').text(response.data.message)
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
        changeTimeTest: function (){
            if(this.input_time_test < 1 || !this.input_time_test){
                this.input_time_test = 1;
            }
        },
        changeNameTopic: function (){
            if(this.input_topic){
                $('#input_topic_error').text('');
            }
        },
        formatTime: function (value){
            return value ? moment(value).format('YYYY-MM-DD') : '';
        },
        checkAllTopic: function (){
            var self = this;
            self.listCheckTopic = [];
            if (!self.check_all) {
                $('.check_once_topic').each(function () {
                    $(this).prop('checked', true);
                    self.listCheckTopic.push($(this).data('id'));
                });
            } else {
                $('.check_once_topic').each(function () {
                    $(this).prop('checked', false);
                });
            }
        },
        checkOneTopic: function (id){
            var self = this;
            var index = self.listCheckTopic.indexOf(id);
            if($('#check_topic'+id).prop('checked')){
                self.listCheckTopic.push(id);
                if(self.listCheckTopic.length === $('.check_once_topic').length){
                    self.check_all = true;
                }
            }else{
                self.check_all = false;
                self.listCheckTopic.splice(index, 1);
            }
        },
        deleteTopic: function () {
            var self = this;
            if(self.listCheckTopic && self.listCheckTopic.length === 0){
                alert('Please select at least one question');
                return;
            }else{
                var confirmDelete = confirm('Are you sure you want to delete?');
                if(confirmDelete) {
                    var self = this;
                    axios.defaults.baseURL = baseUrl;
                    axios.defaults.headers.common.authorization = localStorage.getItem('trial_test_token');
                    $('body').LoadingOverlay('show');
                    axios.post('/admin/trial-test/delete-topics', {
                        list_topic: self.listCheckTopic
                    }).then((response) => {
                        if (response.data.code === '10000') {
                            if(self.check_all == true){
                                if(self.pagination.current_page == self.pagination.last_page && self.pagination.last_page > 1){
                                    self.pagination.current_page = self.pagination.current_page - 1;
                                }
                            }
                            self.initData();
                            self.check_all = false;
                            $('.check_once_topic').each(function () {
                                $(this).prop('checked', false);
                            });
                        }else if(response.data.code === '10002'){
                            alert(response.data.message);
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
                }
            }
        },
        openModalCreateTopic: function (){
            var self = this;
            self.action_type = 1;
            self.id_topic_edit = null;
            self.input_topic = '';
            self.input_folder = 'Trial';
            self.input_test_type = 'COMMON';
            self.input_level = 1;
            self.input_time_test = 1;
            self.input_publish_status = 1;
            $('#modal_topic_trial_test').modal('show');
        },
        openModalEditTopic: function (idTopic){
            var self = this;
            self.action_type = 2;
            self.id_topic_edit = idTopic;
            axios.defaults.baseURL = baseUrl;
            const auth_token = localStorage.getItem('trial_test_token');
            axios.defaults.headers.common.authorization = auth_token;
            $('body').LoadingOverlay('show');
            axios.get( '/admin/trial-test/data-topic', {
                params: {
                    id_topic: idTopic,
                },
            }).then((response) => {
                  if(response.data.code === '10000'){
                    self.input_topic = response.data.data.topic;
                    self.input_folder = response.data.data.folder;
                    self.input_test_type = response.data.data.test_type;
                    self.input_level = response.data.data.level;
                    self.input_time_test = response.data.data.test_time;
                    self.input_publish_status = response.data.data.publish_status;
                    self.input_tag = response.data.data.tags;
                    $("#input_tag").val(self.input_tag).trigger('chosen:updated');
                    $('#modal_topic_trial_test').modal('show');
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
        getTitleByValueForPublishStatus: function(value) {
            var self = this;

            if (!value) {
                return;
            }

            let title = '';
            self.arr_publish_status.forEach(element => {
                if (element.value == value) {
                    title = element.title;
                    return;
                }
            });

            return title;
        },
        changePage: function(page) {
            this.pagination.current_page = page;
            this.initData();
        },
        actionFilter: function () {
            this.pagination.current_page = 1;
            this.initData();
        }
    }
});
