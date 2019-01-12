<!DOCTYPE HTML>
<html>
    <head>        
        <link rel="stylesheet" type="text/css" href="/css/ie10.css" />
        <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="/css/jquery.mCustomScrollbar.css" /> 
        <link rel="stylesheet" type="text/css" media="screen" href="/css/style.css" />
        <!--[if gt IE 7]>
            <link rel="stylesheet" type="text/css" href="/css/ie9.css" />
        <![endif]--> 
        <!--[if IE 7]>
            <link rel="stylesheet" type="text/css" href="/css/ie.css" />
        <![endif]-->  
        <!--[if IE 8]>
            <style type="text/css">
                #chapters{height: 530px !important;}
                div.ui-layout-toggler-content span.content-open, div.ui-layout-toggler-content span.content-closed, div.ui-layout-toggler-playlist span.content-open, div.ui-layout-toggler-playlist span.content-closed, div.ui-layout-toggler-reseller span.content-open, div.ui-layout-toggler-reseller span.content-closed, div.ui-layout-toggler-east span.content-closed{background-position: 5% 50%;}
            </style>
        <![endif]-->                  
        <!--[if !IE]><!-->
        <script>  
            if (/*@cc_on!@*/false) {  
                document.documentElement.className+=' ie10';  
            }  
        </script>
        <!--<![endif]-->  
        <script src="/js/jQuery-2.1.4.min.js" type="text/javascript"></script>
        <script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script src="/js/smh.min.js" type="text/javascript"></script>
        <script src="/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/js/jquery.mCustomScrollbar.min.js" type="text/javascript"></script>
    </head>
    <body style="background-color: #FFFFFF !important; padding: 0 !important;">
        <div id="player-wrapper" style="margin-left: auto; margin-right: auto; width: <?php echo $_GET['wrapper_width'] ?>px;">
            <div id="smh_survey_player" style="width:<?php echo $_GET['width'] ?>px;height:<?php echo $_GET['height'] ?>px;"></div>  
        </div>
        <div style="clear:both;"></div>
        <div class="tabbable tabs-left" style="margin-left: auto; margin-right: auto; width: 778px; margin-top: 30px;">
            <ul class="nav nav-tabs" id="myTab">
                <li class="active"><a href="#edit" data-toggle="tab">Edit</a></li>
                <li><a href="#embed" data-toggle="tab">Embed</a></li>
            </ul>
            <div class="tab-content" style="width: 632px; margin: 15px auto;">
                <div id="edit" class="tab-pane active">
                    <div style="padding-bottom: 10px;">
                        <div style="float:left;margin-right:10px;"><button class="btn btn-disabled" id="updatesurvey-preview" disabled="disabled">Update Preview</button></div><div style="float:left;"><button class="btn btn-disabled" id="savesurvey-config" disabled="disabled">Save Player</button></div><div id="survey-result" style="float:left;margin-left: 35px; margin-top: 7px;"></div>
                        <div class="clear"></div>
                    </div>
                    <div id="survey-options-wrapper" style="overflow-y: auto; margin-bottom: 10px; max-height: 300px;">
                        <table class="table table-bordered table-striped" style="width: 99% !important;">
                            <thead>
                            <th style="width:140px">Attribute</th>
                            <th style="width:160px">Value</th>
                            <th style="width:180px">Description</th>
                            </thead>
                            <tbody>
                                <tr><td>player</td><td><div id="player_select"></div></td><td>The player to use with your chapters.</td></tr>
                                <tr><td>deliveryType</td><td><select id="survey-streamer" class="state form-control" style="width: 180px;"><option id="0" value="rtmp">rtmp</option><option id="1" value="http">http</option></select></td><td>The flash delivery type.</td></tr>
                                <tr><td>backgroundHexColor</td><td><input type="text" id="survey-hexcolor" class="state form-control" value="#<?php echo $_GET['hexcolor'] ?>" style="width: 166px !important;" /></td><td>Hex color value (in the form of: #ffffff) indicating the background color of the survey overlay.</td></tr>
                                <tr><td>backgroundAlpha</td><td><input type="text" id="survey-alpha" class="state form-control" value="<?php echo $_GET['alpha'] ?>" style="width: 166px !important;" /></td><td>Float value (0 to 1) indicating the opacity level of the survey overlay.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="embed" class="tab-pane">
                    <div class="tabbable"> <!-- Only required for left/right tabs -->
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab1" data-toggle="tab">Embed Code</a></li>
                            <li><a href="#tab2" data-toggle="tab">LimeSurvey Code</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab1">
                                <div>
                                    <div style="width: 632px; text-align: center; font-weight: bold; margin-top: 33px;">Player Embed Code:</div>
                                    <div style="text-align: center; padding-top: 10px; height: 177px; width: 502px; margin-left: auto; margin-right: auto;"><textarea id="embed_survey_code" class="form-control" style="width: 500px !important; resize: none;" cols="37" rows="7"></textarea></div>
                                    <div id="result-select"></div>
                                    <div style="width: 632px; text-align: center; padding-top: 18px;"><button class="btn btn-default" id="select-bttn">Select Code</button></div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab2">
                                <div>
                                    <div style="width: 632px; text-align: center; font-weight: bold; margin-top: 24px;">Place the code below in your LimeSurvey's "completed.pstpl" template file<br /> if you would like the player to continue playing after completing the survey(s).</div>
                                    <div style="margin-left: -81px; text-align: center; font-size: 12px; font-weight: bold;">LimeSurvey Code:</div>
                                    <div style="text-align: center; padding-top: 10px; height: 177px; width: 502px; margin-left: auto; margin-right: auto;"><textarea id="limesurvey_code" class="form-control" style="width: 500px !important; resize: none;" cols="37" rows="7"></textarea></div>
                                    <div id="lime-result-select"></div>
                                    <div style="width: 632px; text-align: center; padding-top: 10px;"><button class="btn btn-default" id="lime-select-bttn">Select Code</button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/html5/html5lib/v2.66/mwEmbedLoader.php/partner_id/<?php echo $_GET['pid'] ?>"></script>
        <script>
            var survey_array = new Array();
            var survey_player = "<?php echo $_GET['uiconf'] ?>";
            var survey_streamertype = "<?php echo $_GET['streamer'] ?>";
            var survey_entry = "<?php echo $_GET['eid'] ?>";
            var survey_hexcolor = "<?php echo $_GET['hexcolor'] ?>";
            var survey_alpha = "<?php echo $_GET['alpha'] ?>";
            
            var width = "<?php echo $_GET['width'] ?>";
            var height = "<?php echo $_GET['height'] ?>";
            
            $('#survey-options-wrapper').mCustomScrollbar({
                theme:"inset-dark",
                scrollButtons:{
                    enable: true
                }
            });
            
            mw.setConfig('Kaltura.EnableEmbedUiConfJs', true);
            mw.setConfig('debug', true);
            kWidget.embed({
                targetId: "smh_survey_player",
                wid: "_<?php echo $_GET['pid'] ?>",
                uiconf_id: survey_player,
                entry_id: survey_entry,
                flashvars: {
                    "streamerType" : survey_streamertype,
                    "limeSurveyCuePointForms": {
                        "plugin" : true,
                        "path" : "/p/<?php echo $_GET['pid'] ?>/sp/<?php echo $_GET['pid'] ?>00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                        "relativeTo" : "video",
                        "position" : "before",
                        "onPageJs1" : "{onPagePluginPath}/limeSurveyCuePointForms/limeSurveyCuePointForms.js",
                        "onPageCss1" : "{onPagePluginPath}/limeSurveyCuePointForms/limeSurveyCuePointForms.css",
                        "tags" : "limeSurvey",
                        "backgroundHexColor" : "#"+survey_hexcolor,
                        "backgroundAlpha" : survey_alpha
                    }
                },
                params: {
                    'wmode': 'transparent'
                }
            });
            
            function getPlayerSkins(p_id){
                var sessData = {
                    ks: "<?php echo $_GET['ks'] ?>",
                    partner_id: p_id,
                    type: "player"
                };

                var reqUrl = "/index.php/kmc/getuiconfs"; 
                var players;
                $.ajax({
                    cache:  false,
                    url:    reqUrl,
                    async:  false,
                    type:   'POST',
                    data:   sessData,
                    dataType:   'json',
                    success:function(data) {
                        players =  data;
                    }          
                });    
                return players;
            }
            
            function getPlayers(){
                var players = getPlayerSkins(<?php echo $_GET['pid'] ?>);   

                var player_select = "<select id='survey_players' class='state form-control' style='width: 180px;'>";
    
                i=0;
                $.each(players, function(key, value) {
                    if(value['id'] == '6709584' || value['id'] == '6709796'){} else{
                        survey_array[i] = new Array(value['id'],value['width'],value['height']);
                        player_select += "<option id='"+i+"' value='"+value['id']+"'>"+value['name']+"</option>";
                        i++;
                    }        
                });    
                player_select += "</select>";  
                
                return player_select;
            }
            
            function getSurveyScriptEmbed(){
                var player_script = '<script>if( !window.jQuery ){document.write(\'<script type="text/javascript" src="http://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.66/resources/jquery/jquery.min.js"><\\/script>\');}<\/script><div id="smh_survey_player" style="width:'+width+'px;height:'+height+'px;"></div>\
<script src="http://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.66/mwEmbedLoader.php/partner_id/<?php echo $_GET['pid'] ?>"><\/script>\
<script>\
mw.setConfig(\'Kaltura.EnableEmbedUiConfJs\', true);\
kWidget.embed({\
targetId: "smh_survey_player",\
wid: "_<?php echo $_GET['pid'] ?>",\
uiconf_id: "'+survey_player+'",\
entry_id: "'+survey_entry+'",\
flashvars: {\
"streamerType" : "'+survey_streamertype+'",\
"limeSurveyCuePointForms": {\
"plugin" : true,\
"path" : "/p/<?php echo $_GET['pid'] ?>/sp/<?php echo $_GET['pid'] ?>00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",\
"relativeTo" : "video",\
"position" : "before",\
"onPageJs1" : "{onPagePluginPath}/limeSurveyCuePointForms/limeSurveyCuePointForms.js",\
"onPageCss1" : "{onPagePluginPath}/limeSurveyCuePointForms/limeSurveyCuePointForms.css",\
"tags" : "limeSurvey",\
"backgroundHexColor" : "#'+survey_hexcolor+'",\
"backgroundAlpha" : "'+survey_alpha+'"\
}\
}\
});\
<\/script>';
        return player_script;
    }
    
    function saveSurveyPlayer(){
        var cb2 = function (success, results){
            if(!success)
                alert(results);

            if(results.code && results.message){
                alert(results.message);
                return;
            }    

            $('#survey-result').html('<span class="label label-success">Player Successfully Saved!</span>');
            setTimeout(function(){
                $('#survey-result').html('');
            },3000); 
        };
    
        var cb1 = function (success, results){
            if(!success)
                alert(results);

            if(results.code && results.message){
                alert(results.message);
                return;
            }     
        
            var baseEntry = new KalturaBaseEntry();   
            if(results['partnerData']){
                var pData = $.parseJSON(results['partnerData']);
                var chapter = false;
                var chapterData = '';
            
                $.each(pData, function(key1, value1) {
                    if(key1 == 'chaptersConfig'){
                        chapter = true;
                        $.each(value1, function(key2, value2) {
                            chapterData = '"chaptersConfig":[{"player":"'+value2.player+'","streamerType":"'+value2.streamerType+'","layout":"'+value2.layout+'","containerPosition":"'+value2.containerPosition+'","overflow":"'+value2.overflow+'","includeThumbnail":"'+value2.includeThumbnail+'","thumbnailWidth":"'+value2.thumbnailWidth+'","horizontalChapterBoxWidth":"'+value2.horizontalChapterBoxWidth+'","thumbnailRotator":"'+value2.thumbnailRotator+'","includeChapterNumberPattern":"'+value2.includeChapterNumberPattern+'","includeChapterStartTime":"'+value2.includeChapterStartTime+'","includeChapterDuration":"'+value2.includeChapterDuration+'","pauseAfterChapter":"'+value2.pauseAfterChapter+'","titleLimit":"'+value2.titleLimit+'","descriptionLimit":"'+value2.descriptionLimit+'"}]';
                        }); 
                    }              
                });
            
                if(chapter){
                    baseEntry.partnerData = '{'+chapterData+',"surveyConfig":[{"player":"'+survey_player+'","streamerType":"'+survey_streamertype+'","backgroundHexColor":"'+survey_hexcolor+'","backgroundAlpha":"'+survey_alpha+'"}]}';
                } else {
                    baseEntry.partnerData = '{"surveyConfig":[{"player":"'+survey_player+'","streamerType":"'+survey_streamertype+'","backgroundHexColor":"'+survey_hexcolor+'","backgroundAlpha":"'+survey_alpha+'"}]}';
                }
            } else {
                baseEntry.partnerData = '{"surveyConfig":[{"player":"'+survey_player+'","streamerType":"'+survey_streamertype+'","backgroundHexColor":"'+survey_hexcolor+'","backgroundAlpha":"'+survey_alpha+'"}]}';            
            }
            client.baseEntry.update(cb2, entryId, baseEntry);        
        };    
    
        $('#survey-result').html('<span class="label label-success">Saving Player...</span>');
        var entryId = survey_entry;
        var baseEntry = new KalturaBaseEntry();    
        client.baseEntry.get(cb1, entryId, baseEntry);
    }

    $(document).ready(function(){
        config = new KalturaConfiguration(Number(<?php echo $_GET['pid'] ?>));
        config.serviceUrl = "https://mediaplatform.streamingmediahosting.com/";
        client = new KalturaClient(config);    
        client.ks = "<?php echo $_GET['ks'] ?>";
    
        $('#player_select').html(getPlayers());
        var player_script = getSurveyScriptEmbed();
        $('#embed_survey_code').text(player_script);
        
        $('#tab1').on('click', '#select-bttn', function(event) {
            $('#embed_survey_code').select();  
            $('#result-select').css({
                "display":"block",
                "margin-top":"15px",
                "font-weight":"bold",
                "width":"632px",
                "text-align":"center"
            });
            $('#result-select').html('<span class="label label-info">Press Ctrl+C to copy embed code (Command+C on Mac)</span>'); 
        });
        
        $('#tab2').on('click', '#lime-select-bttn', function(event) {
            $('#limesurvey_code').select();  
            $('#lime-result-select').css({
                "display":"block",
                "margin-top":"10px",
                "font-weight":"bold",
                "width":"632px",
                "text-align":"center"
            });
            $('#lime-result-select').html('<span class="label label-info">Press Ctrl+C to copy embed code (Command+C on Mac)</span>'); 
        });
        
        $("select#survey_players").val(survey_player); 
        
        if(survey_streamertype == 'rtmp'){
            $("select#survey-streamer").find("option#0").attr("selected", true); 
        } else {
            $("select#survey-streamer").find("option#1").attr("selected", true);
        }  
        
        $('#edit').on('change', '.state', function(){ 
            $('#updatesurvey-preview').removeClass("btn-disabled");
            $('#updatesurvey-preview').addClass("btn-default");
            $('#updatesurvey-preview').removeAttr("disabled");       
            $('#savesurvey-config').removeClass("btn-disabled");
            $('#savesurvey-config').addClass("btn-default");
            $('#savesurvey-config').removeAttr("disabled");             
            $('#lime-result-select').css("display","none"); 
        });
        
        $('#edit').on('click', '#savesurvey-config', function(){ 
            survey_streamertype = $('select#survey-streamer option:selected').val();
            survey_player = $('select#survey_players option:selected').val();
            survey_hexcolor = $('#survey-hexcolor').val();
            survey_alpha = $('#survey-alpha').val();
            saveSurveyPlayer();
        });
        
        $('#updatesurvey-preview').click(function(event) {
            survey_streamertype = $('select#survey-streamer option:selected').val();
            survey_player = $('select#survey_players option:selected').val();
            survey_hexcolor = $('#survey-hexcolor').val();
            survey_alpha = $('#survey-alpha').val();
            kWidget.destroy('smh_survey_player');
            var width;
            var height;
            $.each(survey_array, function(key, value) {
                if(survey_player == value[0]){
                    width = value[1];
                    height = value[2];
                }            
            });
        
            var wrapper_width;
            wrapper_width = Number(width); 
        
            $('#player-wrapper').css('width',wrapper_width);
            $('#smh_survey_player').css({
                'width':width,
                'height':height
            });
            kWidget.embed({
                targetId: "smh_survey_player",
                wid: "_<?php echo $_GET['pid'] ?>",
                uiconf_id: survey_player,
                entry_id: survey_entry,
                flashvars: {
                    "streamerType" : survey_streamertype,
                    "limeSurveyCuePointForms": {
                        "plugin" : true,
                        "path" : "/p/<?php echo $_GET['pid'] ?>/sp/<?php echo $_GET['pid'] ?>00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                        "relativeTo" : "video",
                        "position" : "before",
                        "onPageJs1" : "{onPagePluginPath}/limeSurveyCuePointForms/limeSurveyCuePointForms.js",
                        "onPageCss1" : "{onPagePluginPath}/limeSurveyCuePointForms/limeSurveyCuePointForms.css",
                        "tags" : "limeSurvey",
                        "backgroundHexColor" : survey_hexcolor,
                        "backgroundAlpha" : survey_alpha
                    }
                },
                params: {
                    'wmode': 'transparent'
                }
            });

            var player_script = getSurveyScriptEmbed();
            $('#embed_survey_code').text(player_script);    
            var theFrame = $("#survey-view", parent.document.body);
            if(Number(wrapper_width) < 778){
                theFrame.width(778);
                theFrame.height(Number(height)+417);
            } else {
                theFrame.width(Number(wrapper_width)+30);
                theFrame.height(Number(height)+417);           
            }
        });
        
        $('#limesurvey_code').text('<table width="75%"  align="center" style="background: none;">\n\
    <tr>\n\
        <td align="center">\n\
            <font size="2">\n\
                <script>\n\
                    function getUrlParams() {\n\
                        var params = { };\n\
                        document.referrer.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {\n\
                        params[key] = value;\n\
                        });\n\
                        return params;\n\
                    }\n\
                    var params = getUrlParams();\n\
                    parent.postMessage(JSON.stringify( { \'status\':\'ok\', \'playerId\': params.playerId} ), \'*\' );\n\
                <\/script>\n\
            </font>\n\
        </td>\n\
    </tr>\n\
</table>');
                    }); 
        </script>
    </body>
</html>