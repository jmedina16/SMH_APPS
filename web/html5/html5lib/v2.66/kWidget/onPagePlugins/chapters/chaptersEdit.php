<html>
    <head>
        <script src="/js/jQuery-2.1.4.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="../../../mwEmbedLoader.php/partner_id/<?php echo $_GET['pid'] ?>"></script>
        <script type="text/javascript" src="../../../docs/js/bootstrap.js"></script>
        <script>
            // You can improve performance, by coping settings to your uiConf and removing this flag
            mw.setConfig('Kaltura.EnableEmbedUiConfJs', true);
        </script>
        <style type="text/css">
            input[type="text"]{height: 27px !important;}
        </style>
    </head>
    <body style="background-color: #FFFFFF; padding: 0 !important;"> 
        <div>
            This plugin inserts and displays Chapters over video cue-points.
            <div id="player_chapters_edit" style="width:400px;height:330px;float:left;padding-bottom: 10px;"></div>     
            <div id="k-chapterProp" style="float:left;width:370px;padding-left:10px;"></div>
            <div style="clear:both"></div>
            <div id="k-chapterTimeline" style="width:765px"></div>
        </div>
        <script>
            kWidget.featureConfig({
                targetId: "player_chapters_edit",
                wid: "_<?php echo $_GET['pid'] ?>",
                uiconf_id: "6709796",
                entry_id: "<?php echo $_GET['eid'] ?>",
                flashvars: {
                    "streamerType" : "rtmp",
                    "chaptersEdit": {
                        "plugin" : true,
                        "path" : "/p/<?php echo $_GET['pid'] ?>/sp/<?php echo $_GET['pid'] ?>00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                        "ks" : '<?php echo $_GET['ks'] ?>',
                        "includeInLayout" : false,
                        "relativeTo" : "video",
                        "position" : "before",
                        "onPageJs1" : "{onPagePluginPath}/chapters/chaptersEdit.js",
                        "onPageJs2" : "{onPagePluginPath}/chapters/kWidget.cuePointsDataController.js",
                        "onPageCss1" : "{onPagePluginPath}/chapters/chaptersEdit.css",
                        "requiresJQuery" : true,
                        "customDataFields" : "desc,thumbUrl",
                        "tags" : "chaptering",
                        "editPropId" : "k-chapterProp",
                        "editTimelineId" : "k-chapterTimeline"
                    }
                },
                params: {
                    "wmode": "transparent"
                }
            });
        </script>
    </body>
</html>