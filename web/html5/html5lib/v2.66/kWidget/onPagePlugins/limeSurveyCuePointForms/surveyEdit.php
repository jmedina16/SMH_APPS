<!DOCTYPE HTML>
<html>
    <head>
        <script src="/js/jQuery-2.1.4.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="../../../mwEmbedLoader.php/partner_id/<?php echo $_GET['pid'] ?>"></script>
        <script type="text/javascript" src="../../../docs/js/bootstrap.js"></script>
        <script type="text/javascript">	
            // Enable uiconf js which includes external resources 
            mw.setConfig('Kaltura.EnableEmbedUiConfJs', true);            
        </script>
        <style type="text/css">
            input[type="text"]{height: 18px !important;}
        </style>
    </head>
    <body style="background-color: #FFFFFF; padding: 0 !important;">
        <div>
            This plugin loads <a href="http://www.limesurvey.org/" target="_blank">LimeSurvey</a> survey iframes over video cue-points.
            <div id="player_survey_edit" style="width:400px;height:330px;float:left;padding-bottom: 10px;"></div>     
            <div id="k-chapterProp" style="float:left;width:370px;padding-left:10px;"></div>
            <div style="clear:both"></div>
            <div id="k-chapterTimeline" style="width:765px"></div>
        </div>
        <script>
            kWidget.featureConfig({
                'targetId' : 'player_survey_edit',
                'wid' : "_<?php echo $_GET['pid'] ?>",
                'uiconf_id' : '6709796',
                'entry_id' : "<?php echo $_GET['eid'] ?>",
                'flashvars': {
                    "streamerType" : "rtmp",
                    'chaptersEdit':{
                        'plugin': true,
                        'path' : "/p/<?php echo $_GET['pid'] ?>/sp/<?php echo $_GET['pid'] ?>00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                        'relativeTo': 'video',
                        'position': 'before',
                        'ks' : '<?php echo $_GET['ks'] ?>',			
                        'cueTags': 'limeSurvey',						
                        'customDataFields': 'limeSurveyURL,fadeOutTimeMs',
                        'tags' : 'limeSurvey',
                        'editPropId': 'k-chapterProp',
                        'editTimelineId': 'k-chapterTimeline',
                        'requiresJQuery' : true,
                        'onPageJs1': "{onPagePluginPath}/limeSurveyCuePointForms/surveyEdit.js",
                        'onPageCss1': "{onPagePluginPath}/limeSurveyCuePointForms/surveyEdit.css",
                        'onPageJs2': "{onPagePluginPath}/limeSurveyCuePointForms/kWidget.cuePointsDataController.js"
                    }
                },
                params: {
                    "wmode": "transparent"
                }
            })
        </script>

    </body>
</html>