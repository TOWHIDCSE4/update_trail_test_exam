@extends('layout.user.main')
@push('css')
<style>
</style>
@endpush
@section('content')
<!-- MAIN CONTENT -->
<div id="ielts_skill_synthesis" class="d-flex justify-content-center pt-5" v-cloak="">
    <div v-if="items">
        <div class="pt-3">
            <button style="min-width: 180px" type="button" class="btn btn-primary" v-on:click.prevent="startTest('IELTS_LISTENING')">IELTS Listening Tests</button>
            <button v-if="items?.ielts_listening?.has_been_completed" style="min-width: 150px" type="button" class="btn btn-success ml-5" v-on:click.prevent="openTestResults('IELTS_LISTENING')">Xem kết quả</button>
        </div>
        <div class="pt-3">
            <button style="min-width: 180px" type="button" class="btn btn-primary" v-on:click.prevent="startTest('IELTS_READING')">IELTS Reading Tests</button>
            <button v-if="items?.ielts_reading?.has_been_completed" style="min-width: 150px" type="button" class="btn btn-success ml-5" v-on:click.prevent="openTestResults('IELTS_READING')">Xem kết quả</button>
        </div>
        <div class="pt-3">
            <button style="min-width: 180px" type="button" class="btn btn-primary" v-on:click.prevent="startTest('IELTS_WRITING')">IELTS Writing Tests</button>
            <button v-if="items?.ielts_writing?.has_been_completed" style="min-width: 150px" type="button" class="btn btn-success ml-5" v-on:click.prevent="openTestResults('IELTS_WRITING')">Xem lại bài</button>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="/js/user/trial_test/ielts_skill_synthesis.js?v={{config('common.version')}}"></script>
@endpush