@extends('layout.admin.main')
@section('content')
    <style>
        .chosen-container {
            width: 100% !important;
        }
        .chosen-container-multi .chosen-choices {
            padding: 0.375rem 0.75rem !important;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .chosen-container-multi .chosen-choices li.search-choice .search-choice-close {
            width: 12px !important;
            height: 12px !important;
            background: url(http://localhost:9000/css/library/chosen-sprite.png) 0 0 no-repeat !important;
            background-size: 13px 13px !important;
            border-radius: 10px !important;
        }
    </style>
    <link href="/css/library/chosen.min.css" rel="stylesheet"/>
    <div id="list_trial_test" class='container-trial-test'>
        <div class="title-trial-test">Topic List</div>
        <div class="row">
            <div class="col-sm-4 area-btn-action">
                <button class="btn btn-ispeak btn-primary" style="height: 38px" data-toggle="modal" data-target="#modal_topic_trial_test" @click="openModalCreateTopic()">Create
                    New
                </button>
                <button class="btn btn-ispeak btn-danger" style="height: 38px" @click="deleteTopic">Delete</button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 row" style="justify-content: flex-end;align-items: center;padding: 0;margin-right: 5px; margin-top: 10px">
                <div class="col-sm-4 filter-component ">
                    <span class="">Search topic name</span>
                    <div class="area-btn-action">
                        <input type="search" class="form-control" id="site-search" name="q" v-model="name_filter">
                        <button class="btn btn-ispeak btn-primary" style="min-width: 60px;margin-left: 10px; padding: 6px 0" @click="actionFilter()">Search</button>
                    </div>
                </div>
                <div class="col-sm-1 filter-component">
                    <span class="">Folder</span>
                    <select class="form-control" type="text"  v-model="folder_filter" @change="actionFilter()">
                        <option value="">All</option>
                        <option value="SafeZone">SafeZone</option>
                        <option value="Trial">Trial</option>
                        <option value="Regular">Regular</option>
                        <option value="Trial-ielts">Trial-ielts</option>
                    </select>
                </div>
                <div class="col-sm-1 filter-component">
                    <span class="">Status</span>
                    <select class="form-control" type="text" v-model="status_filter" @change="actionFilter()">
                        <option value="">All</option>
                        <option v-for="status in arr_publish_status" :value="status.value">@{{ status.title }}</option>
                    </select>
                </div>
                <div class="col-sm-2 filter-compon ent">
                    <span class="">Type</span>
                    <select class="form-control" type="text"  v-model="test_type_filter" @change="actionFilter()">
                        <option value="">All</option>
                        <option value="COMMON">COMMON</option>
                        <option value="IELTS_GRAMMAR">IELTS_GRAMMAR</option>
                        <option value="IELTS_LISTENING">IELTS_LISTENING</option>
{{--                        <option value="IELTS_SPEAKING">IELTS_SPEAKING</option>--}}
                        <option value="IELTS_READING">IELTS_READING</option>
                        <option value="IELTS_WRITING">IELTS_WRITING</option>
                    </select>
                </div>
                <div class="col-sm-3 filter-component" v-if="source">
                    <span class="">Tag</span>
                    <select class="form-control" id="filter_tag" name="filter-tag" multiple v-model="tag_filter" @change="actionFilter()">
                        <option value="">All</option>
                        <option v-for="tag in list_tag" :value="tag">@{{ tag }}</option>
                    </select>
                </div>
                <div class="col-sm-3 filter-component" v-if="!source">
                    <span class="">Creator</span>
                    <select class="form-control" type="text" id="search_creator" @change="actionFilter()" v-model="creator_oid">
                        <option v-for="creator in arrayCreator" :value="creator.id">@{{ creator.full_name }} - @{{ creator.user_name }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div>
            <table id="table_list_trial_test">
                <tr>
                    <th style="width: 2%; min-width: 20px"><input type="checkbox" id="checkAllTopic" v-model="check_all" v-on:click="checkAllTopic()" name="check_all_topic"></th>
                    <th style="width: 5%;min-width: 90px">Date</th>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;text-align: left;padding-left: 20px">Topic</th>
                    <th style="width: 7%">Folder</th>
                    <th style="width: 10%">Type</th>
                    <th style="width: 8%;">Level</th>
                    <th style="width: 7%;">Attended</th>
                    <th style="width: 7%;">Num Max</th>
                    <th style="width: 5%;">Average</th>
                    <th style="width: 7%;">Test Time</th>
                    <th style="width: 7%;">Status</th>
                    <th style="width: 12%;">Creator</th>
                    <th style="width: 2%;"></th>
                </tr>
                <tbody id="body_table_topic" hidden>
                    <tr v-for="topic in topics">
                        <td style="width: 50px"><input type="checkbox" name="check_topic[]" :data-id="topic.id" :id="'check_topic'+topic.id" class="check_once_topic"
                                                       v-on:click="checkOneTopic(topic.id)"></td>
                        <td>@{{ formatTime(topic.created_at) }}</td>
                        <td>@{{ topic.id }}</td>
                        <td style="text-align: left;padding: 0 10px">
                            <div class="hover-link-text" v-on:click="window.location.replace('/admin/trial-test/list-questions/'+ topic.id)">
                                @{{ topic.topic }}
                            </div>
                        </td>
                        <td>@{{ topic.folder }}</td>
                        <td><div class="text-truncate" style="width:120px">@{{ topic.test_type }}</div></td>
                        <td>
                            <div v-for="level in array_level" v-show="topic.level == level.value">@{{ level.title }}</div>
                        </td>
                        <td>@{{ topic.number_attended ?? 0 }}</td>
                        <td>@{{ topic.number_max_score ?? 0 }}(@{{ topic.percent_max_score }}%)</td>
                        <td>@{{ topic.average ?? 0 }}</td>
                        <td>@{{ topic.test_time }}</td>
                        <td>@{{ getTitleByValueForPublishStatus(topic.publish_status) }}</td>
                        <td>@{{ topic.name_creator }}</td>
                        <td>
                            <button class="btn btn-light" data-toggle="modal" data-target="#modal_topic_trial_test" @click="openModalEditTopic(topic.id)" style="margin: 5px">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                {{--            <tr>--}}
                {{--                <td colspan="7"> データがありません。</td>--}}
{{--                            </tr> --}}
            </table>
            <nav style="clear:both" id="pagination_topic" class="text-center" hidden>
                <span style="display: block;font-weight: bold;">Showing @{{pagination.from}} to @{{pagination.to}} of @{{pagination.total}} items</span>
                <ul class="pagination" style="justify-content: center !important;">
                    <li v-if="pagination.current_page > 1">
                        <a href="#" aria-label="Previous"
                           @click.prevent="changePage(pagination.current_page - 1)">
                            <span aria-hidden="true" style="color:#08BF5A"><i class="fas fa-arrow-left"></i></span>
                        </a>
                    </li>
                    <li>
                        <a v-if="pagination.current_page > 3" href="#" @click.prevent="changePage(1)">1</a>
                    </li>
                    <li>
                        <a v-if="pagination.current_page > 3" href="#" >...</a>
                    </li>
                    <li v-for="(page,index) in pagination.last_page" v-bind:class="[ page == isActived ? 'active' : '']">
                        <a v-if="((index == pagination.current_page - 2 && index !== pagination.last_page-3) || (index == pagination.current_page-1 && index !== pagination.last_page-3) || index == pagination.current_page) && index < pagination.last_page-3" href="#" @click.prevent="changePage(page)">@{{ page }}</a>
                        <a v-if="index == pagination.current_page+2 && index !== pagination.last_page-1  && pagination.last_page > 5" href="#" >...</a>
                        <a v-if="(index == pagination.last_page-3 || index == pagination.last_page-2) && pagination.current_page > pagination.last_page-4" href="#" @click.prevent="changePage(page)">@{{ page }}</a>
                        <a v-if="index == pagination.last_page-1" href="#" @click.prevent="changePage(page)">@{{ page }}</a>
                    </li>
                    <li v-if="pagination.current_page < pagination.last_page">
                        <a href="#" aria-label="Next" @click.prevent="changePage(pagination.current_page + 1)">
                            <span aria-hidden="true" style="color:#08BF5A"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    </li>
                </ul>
            </nav>


        </div>
        <div id="modal_topic_trial_test" class="modal fade" data-keyboard="false"
             data-backdrop="static"
             tabindex="-1" role="dialog" style="display:none">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div id="" class="modal-header">
                        <i class="fa fa-times hover-icon-action close-modal-create" data-dismiss="modal"></i>
                        <h2 class="text-center" v-if="action_type == 1">Create New Topic</h2>
                        <h2 class="text-center" v-if="action_type == 2">Edit Topic</h2>
                    </div>
                    <div id="modal_body_trial-test" class="modal-body modal-body-main">
                        <div class="inner modal-box-body">
                            <div class="modal-input">
                                <span class="label-modal">Topic</span>
                                <div style="width: 60%;display: grid">
                                    <input class="form-control" type="text" id="input_topic" @change="changeNameTopic()" name="input-topic" v-model="input_topic">
                                    <div class="error-input" id="input_topic_error"></div>
                                </div>
                            </div>
                            <div class="modal-input">
                                <span class="label-modal">Folder</span>
                                <div style="width: 60%;display: grid">
                                    <select class="form-control" id="input_folder" name="input-folder" v-model="input_folder">
                                        <option value="SafeZone">SafeZone</option>
                                        <option value="Trial">Trial</option>
                                        <option value="Regular">Regular</option>
                                        <option value="Trial-ielts">Trial-ielts</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-input">
                                <span class="label-modal">Type</span>
                                <div style="width: 60%;display: grid">
                                    <select class="form-control" id="input_test_type" name="input-test-type" v-model="input_test_type">
                                        <option value="COMMON">COMMON</option>
                                        <option value="IELTS_GRAMMAR">IELTS_GRAMMAR</option>
                                        <option value="IELTS_LISTENING">IELTS_LISTENING</option>
{{--                                        <option value="IELTS_SPEAKING">IELTS_SPEAKING</option>--}}
                                        <option value="IELTS_READING">IELTS_READING</option>
                                        <option value="IELTS_WRITING">IELTS_WRITING</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-input">
                                <span class="label-modal">Level</span>
                                <div style="width: 60%;display: grid">
                                    <select class="form-control" id="input_level" name="input-level" v-model="input_level">
                                        <option v-for="level in array_level" :value="level.value">@{{ level.title }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-input">
                                <span class="label-modal">Tag</span>
                                <div style="width: 60%;display: grid">
                                    <select class="form-control" id="input_tag" name="input-tag" multiple v-model="input_tag">
                                        <option v-for="tag in list_tag" :value="tag">@{{ tag }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-input">
                                <span class="label-modal">Test Time</span>
                                <input class="form-control" type="number" id="input_test_time" name="input-test-time" min="1" v-model="input_time_test" @change="changeTimeTest()" style="width: 60%">
                            </div>
                            <div class="modal-input">
                                <span class="label-modal">Publish Status</span>
                                <div style="width: 60%;display: grid">
                                    <select class="form-control" id="input_publish_status" name="input-publish-status" v-model="input_publish_status">
                                        <option v-for="publish_status in arr_publish_status" :value="publish_status.value">@{{ publish_status.title }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="" class="modal-footer" style="margin-bottom: 50px">
                        <button class="btn btn-ispeak btn-success" type="button" style="width: 100px;margin-right: 0" @click="saveTopicTrialTest()">
                            <span v-if="action_type == 1" style="color: white">Create</span>
                            <span v-if="action_type == 2" style="color: white">Update</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="/js/library/chosen.min.js" ></script>
    <script>
        let listTag = '{{ env('LIST_TAG_TOPIC') }}';
    </script>
    <script src="/js/admin/trial_test/index.js?v={{config('common.version')}}"></script>
@endpush
