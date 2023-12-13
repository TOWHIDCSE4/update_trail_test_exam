<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="format-detection" content="telephone=no">
    <title>EnglishPlus - Test</title>
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
    <link rel="stylesheet" href="/css/library/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin/index.css?v={{config('common.version')}}">
    <style>
        .area-text-editor div, span, label{
            font-weight: normal;
        }

        div, span, label{
            font-weight: bold;
        }
    </style>
    @stack('css')
</head>
<body style="margin-right: 5px">
<div class="col-xs-12 col-sm-12 main" style="padding: 0">
    <div>
@yield('content')
</div>
</div>
</body>
<script src="/js/library/jquery.min.js" ></script>
<script src="/js/library/moment.min.js" ></script>
<script src="/js/library/popper.min.js" ></script>
<script src="/js/library/vue.min.js" ></script>
<script src="/js/library/bootstrap.min.js" ></script>
<script src="/js/library/jquery-ui.min.js" ></script>
<script src="/js/library/axios.min.js" ></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.0/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/plugin/loadingoverlay/loadingoverlay.min.js"></script>
<script src="/js/plugin/loadingoverlay/loadingoverlay_progress.min.js"></script>

<script>
    var baseUrl = '{{ env('BASE_API_URL') }}';
    var baseStorageUrl = '{{ env('BASE_STORAGE_URL') }}';
    var baseApiIspeak = '{{ env('BASE_API_ISPEAK') }}';
    var titleError = 'Please enter this field';
    window.addEventListener("message", function(e) {
        var data = e.data;
        if (!data) data = e.originalEvent.data;
        if ("requestHeightChange" == data) {
            var body = document.body,
                html = document.documentElement;
            var height = Math.max(body.scrollHeight, body.offsetHeight,
                html.clientHeight, html.scrollHeight, html.offsetHeight);
            window.parent.postMessage("setHeight:" + height, "*");
        }
    });
    function callApiUpImageTextEditor(blobInfo, success, failure, progress){
        const formData = new FormData();
        formData.append('file', blobInfo.blob());
        axios.defaults.baseURL = baseStorageUrl;
        axios.defaults.headers.common.authorization = localStorage.getItem('trial_test_token');
        axios.post( '/admin/lib-test/upload', formData)
            .then((response) => {
                if(response.data.code === '10000'){
                    success(response.data.data)
                }else{
                    alert('upload failed!')
                }
            })
            .catch((errors) => {
                $('body').LoadingOverlay('hide');
                console.log(errors); // Errors
                failure(errors)
            });
    }

    // function callAxiosGet(url ,params){
    //     axios.defaults.baseURL = baseUrl;
    //     var auth_token = localStorage.getItem('trial_test_token');
    //     axios.defaults.headers.common.authorization = auth_token;
    //     axios.get( url, {
    //         params
    //     }).then((response) => {
    //         console.log(response.data.code);
    //         if(response.data.code === '10000'){
    //             console.log(1);
    //             return response.data;
    //         }else{
    //             return false;
    //         }
    //     }).catch((errors) => {
    //         console.log(errors); // Errors
    //         // return errors.message;
    //         return false;
    //     });
    // }
</script>
@stack('script')
</html>
