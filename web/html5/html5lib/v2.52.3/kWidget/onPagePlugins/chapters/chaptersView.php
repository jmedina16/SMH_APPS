<!DOCTYPE HTML>
<html>
    <head>
        <!-- Testing URL, production usage should use production urls! -->
    </head>
    <body style="background-color: #F8FAFB; padding: 0 !important;">
        <div id="smh_player" style="width:400px;height:330px;"></div>
        <script>
            if( !window.jQuery ){
                document.write('<script type="text/javascript" src="/html5/html5lib/v2.52.3/resources/jquery/jquery.min.js"><\/script>');
            }         
        </script>
        <script src="/html5/html5lib/v2.52.3/mwEmbedLoader.php/partner_id/<?php echo $_GET['pid'] ?>"></script>
        <script>
            mw.setConfig('Kaltura.EnableEmbedUiConfJs', true);
            kWidget.embed({
                targetId: "smh_player",
                wid: "_<?php echo $_GET['pid'] ?>",
                uiconf_id: "6709796",
                entry_id: "<?php echo $_GET['eid'] ?>",
                flashvars: {
                    "streamerType" : 'rtmp',
                    "chaptersView": {
                        "plugin" : true,
                        "path" : "/p/10012/sp/100200/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                        "includeInLayout" : false,
                        "relativeTo" : "video",
                        "position" : "after",
                        "onPageJs1" : "{onPagePluginPath}/chapters/chaptersView.js",
                        "onPageJs2" : "{onPagePluginPath}/libs/jcarousellite.js",
                        "onPageJs3" : "{onPagePluginPath}/libs/jquery.sortElements.js",
                        "onPageCss1" : "{onPagePluginPath}/chapters/chaptersView.css",
                        "containerId" : "",
                        "tags" : "chaptering",
                        "layout" : "vertical",
                        "containerPosition" : "right",
                        "overflow" : false,
                        "includeThumbnail" : true,
                        "thumbnailWidth" : "100",
                        "horizontalChapterBoxWidth" : "320",
                        "thumbnailRotator" : true,
                        "includeChapterStartTime" : true,
                        "includeChapterDuration" : true,
                        "pauseAfterChapter" : false,
                        "titleLimit" : "24",
                        "chapterRenderer" : "onChapterRenderer",
                        "chaptersRenderDone" : "onChaptersRenderDone"
                    }
                }
            });
        </script>
    </body>
</html>