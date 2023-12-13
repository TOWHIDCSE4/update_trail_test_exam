<div class="row area-btn-action">
    <button class="btn btn-ispeak btn-primary" @click="window.location.replace('/admin/trial-test/create-question/{{ $topic_id }}/'+ item.id)">Create
        New
    </button>
    <button class="btn btn-ispeak btn-danger" @click="deleteQuestion">Delete</button>
</div>
<div>
    <table id="table_list_trial_test">
        <tr>
            <th style="width: 2%; min-width: 30px"><input type="checkbox" id="checkAllQuestion" v-model="item.check_all" v-on:click="checkAllQuestion()" :name="'check_all_question'+item.id"></th>
            <th style="width: 5%;min-width: 100px">Date</th>
            <th style="width: 30%;text-align: left;padding-left: 50px">Title</th>
            <th style="width: 8%;min-width: 100px">Type</th>
            <th style="width: 10%;text-align: right;padding-right: 4%">Attended</th>
            <th style="width: 20%;min-width: 180px"></th>
        </tr>
        <tbody class="sort_table_question" :id="'sort_table_question'+indexSection">
        <tr v-for="(question, index) in item.questions" class="sort-question" :data-id="question.question_id"  :data-index="question.order" style="cursor: move">
            <td style="width: 50px"><input type="checkbox" :name="'check_question'+indexSection+'[]'" :data-id="question.question_id" :id="'check_question'+indexSection+'_'+question.question_id" :class="'check_once_question'+ indexSection"
                                           v-on:click="checkOneQuestion(question.question_id)"></td>
            <td>@{{ formatTime(question.created_at) }}</td>
            <td style="text-align: left;padding-left: 15px">
                <div style=" overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 34vw;">
                    <span class="hover-link-text" v-on:click="window.location.replace('/admin/trial-test/edit-question/'+ {{ $topic_id }} +'/'+ item.id +'/'+ question.question_id)">@{{ question.title }}</span>
                </div>
            </td>
            <td>
                <div v-if="question.category == 1">Vocabulary</div>
                <div v-if="question.category == 2">Reading</div>
                <div v-if="question.category == 3">Writing</div>
                <div v-if="question.category == 4">Grammar</div>
{{--                <div v-if="question.category == 5">Listening</div>--}}
                <div v-if="question.category == 6">IELTS Writing</div>
            </td>
            <td style="text-align: right;padding-right: 4%">@{{ question.number_attended }}</td>
            <td style="min-with:200px">
                <div style="position: relative">
                    <div style="position: absolute">@{{ question.number_correct }} Correct</div>
                    <div style="position: absolute;right: 0">@{{ question.number_incorrect }} Incorrect</div>
                </div>
                <div class="progress">
                    <div class="progress-bar progress-result" :style="'width:'+ (question.number_attended > 0 ? (question.number_correct/(question.number_correct + question.number_incorrect))*100 : 0) +'%'" role="progressbar" aria-valuenow="0"
                         aria-valuemin="0" aria-valuemax="0"></div>
                </div>
            </td>
        </tr>
        </tbody>
        {{--                </tr>--}}
        {{--            <tr>--}}
        {{--                <td colspan="7"> データがありません。</td>--}}
        {{--            </tr>--}}
    </table>
</div>
