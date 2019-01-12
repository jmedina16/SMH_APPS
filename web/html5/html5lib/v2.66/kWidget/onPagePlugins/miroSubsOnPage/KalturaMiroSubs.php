<?php
$expire = time() + 60 * 60 * 24 * 1;
setcookie("skiphowto", '1', $expire, '/', 'mediaplatform.streamingmediahosting.com', false);
?>
<!DOCTYPE HTML>
<html>
    <head>
        <script src="/js/jquery.min.js" type="text/javascript"></script>
        <script src="/html5/html5lib/v2.66/kWidget/onPagePlugins/miroSubsOnPage/miroSubs.js" type="text/javascript"></script>
        <link type="text/css" rel="stylesheet" media="screen" href="/html5/html5lib/v2.66/kWidget/onPagePlugins/miroSubsOnPage/mirosubs/media/css/mirosubs-widget.css">
    </head>
    <body>
        <div id="kdoc-more-desc">
            <br>
            <a href="#" id="invokeMiroEditor">Invoke Editor</a>
        </div>
    </body>
</html>
