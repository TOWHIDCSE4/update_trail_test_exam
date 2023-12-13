<div class="col-match-question">
    <div class="component-match-question">
        <ul class="nav nav-tabs area-question-tabs" role="tablist"
            style="border-bottom: 2px solid #51595C;">
            <li role="nav-item"
                style="border-bottom: unset;text-align: center;margin-bottom: 0">
                <a :href="'#text_match_a'+indexMatch" role="tab" data-toggle="tab"
                   class="tab-question-text nav-link " :class="questionMatch.tab_a == 1 ? 'active' : ''" @click="switchTabMatchQuestion('text_match_a', indexMatch)">Text</a>
            </li>
            <li role="nav-item"
                style="border-bottom: unset;text-align: center;margin-bottom: 0">
                <a :href="'#image_match_a'+indexMatch" role="tab" data-toggle="tab"
                   class="tab-question-image nav-link" :class="questionMatch.tab_a == 2 ? 'active': ''" @click="switchTabMatchQuestion('image_match_a', indexMatch)">Picture</a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane" :id="'text_match_a'+indexMatch" :class="questionMatch.tab_a == 1 ? 'active' : ''">
                <textarea class="form-control text-area-match-question" rows="3" v-model="questionMatch.text_content_a"></textarea>
            </div>
            <div role="tabpanel" class="tab-pane" :id="'image_match_a'+indexMatch" :class="questionMatch.tab_a == 2 ? 'active' : ''">
                <div class="picture-area-match-question">
                    <label v-if="!questionMatch.image_url_a" :for="'file_image_match_a'+indexMatch" class="btn btn-primary">Select file</label>
                    <input :id="'file_image_match_a'+indexMatch" @change="changeFile(event,'file_image_match_a', indexMatch)" :name="'file-image-match-a'+indexMatch" ref="fileInput"
                           class="c_h_file-input" type="file" accept="image/*" style="display: none"/>
                    <img v-if="questionMatch.image_url_a" class="img-question-view" :src="questionMatch.image_url_a">
                    <button type="button" v-if="questionMatch.image_url_a" @click="deleteFile('file_image_match_a', indexMatch)" class="btn btn-danger" style="width: 93px; margin-left: 10px">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <div style="min-width: 110px">
        <div class="check-voice" v-if="questionMatch.tab_a == 1">
            <input type="checkbox" class="input-checkbox-question"  v-model="questionMatch.voice_text_content_a">To voice
        </div>
    </div>
    <div class="component-match-question">
        <ul class="nav nav-tabs area-question-tabs" role="tablist"
            style="border-bottom: 2px solid #51595C;">
            <li role="nav-item"
                style="border-bottom: unset;text-align: center;margin-bottom: 0">
                <a :href="'#text_match_b'+indexMatch" role="tab" data-toggle="tab"
                   class="tab-question-text nav-link" :class="questionMatch.tab_b == 1 ? 'active' : ''" @click="switchTabMatchQuestion('text_match_b', indexMatch)">Text</a>
            </li>
            <li role="nav-item"
                style="border-bottom: unset;text-align: center;margin-bottom: 0">
                <a :href="'#image_match_b'+indexMatch" role="tab" data-toggle="tab"
                   class="tab-question-image nav-link" :class="questionMatch.tab_b == 2 ? 'active' : ''" @click="switchTabMatchQuestion('image_match_b', indexMatch)">Picture</a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane" :id="'text_match_b'+indexMatch" :class="questionMatch.tab_b == 1 ? 'active' : ''">
                <textarea class="form-control text-area-match-question" rows="3" v-model="questionMatch.text_content_b"></textarea>
            </div>
            <div role="tabpanel" class="tab-pane" :id="'image_match_b'+indexMatch" :class="questionMatch.tab_b == 2 ? 'active' : ''">
                <div class="picture-area-match-question">
                    <label v-if="!questionMatch.image_url_b" :for="'file_image_match_b'+indexMatch" class="btn btn-primary">Select file</label>
                    <input :id="'file_image_match_b'+indexMatch" @change="changeFile(event,'file_image_match_b',indexMatch)" :name="'file-image-match-b'+indexMatch" ref="fileInput"
                           class="c_h_file-input" type="file" accept="image/*" style="display: none"/>
                    <img v-if="questionMatch.image_url_b" class="img-question-view" :src="questionMatch.image_url_b">
                    <i v-if="questionMatch.image_url_b" class="fas fa-trash-alt trash-main-question hover-icon-action" @click="deleteFile('file_image_match_b', indexMatch)" style="margin-left: 5px"></i>
                </div>
            </div>
        </div>
    </div>
    <div style="width: 255px;display: flex; align-items: center; justify-content: center">
            <div v-if="!questionMatch.audio_url" style="margin-right: 10px">Audio</div>
            <label style="margin-bottom: 0" v-if="!questionMatch.audio_url" :for="'file_audio_match' + indexMatch" class="btn btn-primary">Select file</label>
            <input :id="'file_audio_match' + indexMatch" :name="'file-audio-match' + indexMatch" ref="fileInput" @change="changeFile(event,'file_audio_match',indexMatch)"
                   class="c_h_file-input" type="file" accept="audio/*" style="display: none"/>
        <audio style="min-width: 230px; margin-top: 10px" class="audio-question-view" v-if="questionMatch.audio_url" controls="controls"><source :src="questionMatch.audio_url"></audio>
        <i v-if="questionMatch.audio_url" class="fas fa-trash-alt trash-main-question hover-icon-action" @click="deleteFile('file_audio_match', indexMatch)" style="margin-left: 5px"></i>
    </div>
</div>
<div class="error-question" :id="'question_match_error'+indexMatch"></div>
<div v-if="indexMatch == 0" class="error-question" id="question_match_error"></div>
<hr class="hr-match-question">
