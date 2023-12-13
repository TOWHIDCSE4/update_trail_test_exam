<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en" translate="no">

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
    @stack('css')
</head>

<body>
    <div class="col-xs-12 col-sm-12 main" style="padding: 0">
        <div>
            @yield('content')
        </div>
    </div>

    <script src="/js/library/jquery.min.js"></script>
    <script src="/js/library/popper.min.js"></script>
    <script src="/js/library/vue.min.js"></script>
    <script src="/js/library/bootstrap.min.js"></script>
    <script src="/js/library/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsPlumb/2.15.0/js/jsplumb.min.js"></script>
    <script src="/js/plugin/loadingoverlay/loadingoverlay.min.js"></script>
    <script src="/js/plugin/loadingoverlay/loadingoverlay_progress.min.js"></script>
    <script>
        var baseApiUrl = '{{ env('BASE_API_URL') }}';
        var baseUrl = '{{ env('BASE_URL') }}';

        window.onbeforeunload = function (e) {
            e = e || window.event;

            // For IE and Firefox prior to version 4
            if (e) {
                e.returnValue = 'Sure?';
            }

            // For Safari
            return 'Sure?';
        };

        // window.addEventListener("message", function(e) {
        //     var data = e.data;
        //     console.log("trial_test data: ", data);
        //     if (!data) data = e.originalEvent.data;
        //     if ("requestHeightChange" == data) {
        //         var body = document.body,
        //             html = document.documentElement;

        //         var height = Math.max(body.scrollHeight, body.offsetHeight,
        //             html.clientHeight, html.scrollHeight, html.offsetHeight);
        //         console.log("trial_test height: " + height);
        //         window.parent.postMessage("setHeight:" + height, "*");
        //     }
        // });
    </script>
    @stack('script')
</body>

</html>
