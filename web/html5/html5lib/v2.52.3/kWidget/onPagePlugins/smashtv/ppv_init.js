var ppv_init = function(){
    return this.init();
}

ppv_init.prototype = {
    init: function(p){          
        refresh_player = true;
        current_time = 0;
        is_logged_in = false;
        init_loaded = false;
        userId = 0;
        smh_ppv_order=new Array();
        cat_entries=new Array();
        blocked = false;
        livestream = false;
        paid = false;
        playlist = false;
        category = false;
        media_type = '';
        smh_aff = '';
        protocol = p;
    },
    checkAccess: function(pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type){
        if(refresh_player){
            var smh_sess = $smh.cookie('smh_auth_key');
            if(smh_sess == '' || smh_sess == undefined || smh_sess == null){
                ppv.loadVideo('',pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
            } else {
                var sessData = {
                    auth_key: smh_sess,
                    pid: pid,
                    sm_ak: sm_ak
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=is_logged_in",
                    data: sessData,
                    dataType: 'json'
                }).done(function(data) {
                    if(data['success']){
                        ppv.checkInventory(pid,sm_ak,data['user_id'],uiconf_id,uiconf_width,uiconf_height,entryId,type);
                        is_logged_in = true;
                        userId = data['user_id'];
                    } else {
                        ppv.loadVideo('',pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
                    }                       
                });                   
            }   
        }
    },
    checkInventory: function(pid,sm_ak,uid,uiconf_id,uiconf_width,uiconf_height,entryId,type){
        var sessData = {
            entryId: entryId,
            uid: uid,
            pid: pid,
            sm_ak: sm_ak,
            type: type
        }
        $smh.ajax({
            type: "GET",
            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=check_inventory",
            data: sessData,
            dataType: 'json'
        }).done(function(data) {
            if(!data){
                ppv.loadVideo('',pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
            } else {
                ppv.loadVideo(data,pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);  
                paid = true;
            }                                
        });  
    },
    setupPlayer: function(sm_ak,entryId,type){
        var smh_aff_cookie = $smh.cookie('smh_aff');
        if(smh_aff_cookie != '' || smh_aff_cookie != undefined || smh_aff_cookie != null){
            smh_aff = smh_aff_cookie;
        }
            
        var sessData = {
            entry_id: entryId,
            sm_ak: sm_ak
        }
        $smh.ajax({
            type: "GET",
            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=setup_player",
            data: sessData,
            dataType: 'json'
        }).done(function(data) {
            if(data['ac_type'] == 1){
                blocked = true;
            } else {
                blocked = false;
            }
            
            media_type = data['media_type'];
            
            if(data['media_type'] == 100 || data['media_type'] == 101){
                livestream = true;
            } else if(data['media_type'] == 3){
                playlist = true;
            }
            
            if(type == 'cl' || type == 'cr' || type == 'ct' || type == 'cb'){
                category = true;
                
                var sessData = {
                    cat_id: entryId,
                    sm_ak: sm_ak
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_cat_entries",
                    data: sessData,
                    dataType: 'json'
                }).done(function(data) {
                    cat_entries = data;
                });
            }
        }); 
    },
    get_thumb: function(sm_ak,entryId){
        var sessData = {
            entry_id: entryId,
            sm_ak: sm_ak
        }
        
        var thumb = '';
        $smh.ajax({
            type: "GET",
            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_get_thumb",
            async: false,
            data: sessData,
            dataType: 'json'
        }).done(function(data) {
            thumb = data;
        }); 
        
        return thumb;
    },
    loadVideo: function(ks,pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type){
        if (window.kdp) {
            kWidget.destroy(window.kdp);
            delete(window.kdp);
        }
        var uniqid = +new Date();
        var kdpId = 'smhtarget'+uniqid;

        $smh( '#myVideoContainer' ).html(
            '<div id="' + kdpId + '" style="width:400px;height:330px"></div>'
            );

        flashvars = {};        
        flashvars.externalInterfaceDisabled = false;
        flashvars.autoplay = true;
        flashvars.disableAlerts = true;
        flashvars.streamerType = 'rtmp';      
        flashvars.httpProtocol = protocol;
        if(ks != "") flashvars.ks = ks; 
        
        if(type == 's'){
            flashvars.entryId = entryId;
            flashvars.ppv = {
                "plugin" : true,
                "path" : "/p/"+pid+"/sp/"+pid+"00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                "relativeTo" : "video",
                "position" : "before",                    
                "onPageJs1" : "{onPagePluginPath}/ppv/ppv.js",
                "onPageJs2" : "{onPagePluginPath}/ppv/resources/js/jquery.validate.min.js",
                "onPageJs3" : "{onPagePluginPath}/ppv/resources/js/jstz.min.js",   
                "onPageJs4" : "{onPagePluginPath}/ppv/resources/js/bootstrap.min.js", 
                "onPageCss1" : "{onPagePluginPath}/ppv/resources/css/smh_ppv_style.css", 
                "requiresJQuery" : true,
                "pid" : pid,
                "sm_ak" : sm_ak,
                "uiConfId" : uiconf_id,
                "uiConf_width" : uiconf_width,
                "uiConf_height" : uiconf_height,
                "entryId" : entryId,
                "type" : type
            }
            kWidget.thumbEmbed({
                'targetId': kdpId,
                'wid': '_'+pid,
                'uiconf_id' : uiconf_id,
                'entry_id' : entryId,
                'width': uiconf_width,
                'height': uiconf_height,
                'flashvars': flashvars,
                'thumbReadyCallback': function(){
                    ppv.setupPlayer(sm_ak,entryId,type);
                    window.pptransact.init(false);
                },
                'params':{
                    'wmode': 'opaque'
                }
            }); 
        }else if(type == 'p'){
            flashvars.ppv = {
                "plugin" : true,
                "path" : "/p/"+pid+"/sp/"+pid+"00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                "relativeTo" : "video",
                "position" : "before",                    
                "onPageJs1" : "{onPagePluginPath}/ppv/ppv.js",
                "onPageJs2" : "{onPagePluginPath}/ppv/resources/js/jquery.validate.min.js",
                "onPageJs3" : "{onPagePluginPath}/ppv/resources/js/jstz.min.js",   
                "onPageJs4" : "{onPagePluginPath}/ppv/resources/js/bootstrap.min.js", 
                "onPageCss1" : "{onPagePluginPath}/ppv/resources/css/smh_ppv_style.css", 
                "requiresJQuery" : true,
                "pid" : pid,
                "sm_ak" : sm_ak,
                "uiConfId" : uiconf_id,
                "uiConf_width" : uiconf_width,
                "uiConf_height" : uiconf_height,
                "entryId" : entryId,
                "type" : type
            }
            flashvars.playlistAPI = {
                'autoPlay': false, 
                'autoContinue': true, 
                'autoInsert':true, 
                'kpl0Name' : "PPV Playlist", 
                'kpl0Url' : protocol+"://mediaplatform.streamingmediahosting.com/index.php/partnerservices2/executeplaylist?uid=&partner_id="+pid+"&subp_id="+pid+"00&format=8&ks={ks}&playlist_id="+entryId
            }
            kWidget.thumbEmbed({
                'targetId': kdpId,
                'wid': '_'+pid,
                'uiconf_id' : uiconf_id,
                'width': uiconf_width,
                'height': uiconf_height,
                'flashvars': flashvars,
                'thumbReadyCallback': function(){
                    ppv.setupPlayer(sm_ak,entryId,type);
                    window.pptransact.init(false);
                    var entry_id = ppv.get_thumb(sm_ak,entryId);
                    $smh('.kWidgetCentered').attr('src',protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/modules/KalturaSupport/thumbnail.php/p/'+pid+'/sp/'+pid+'/entry_id/'+entry_id+'/uiconf_id/'+uiconf_id+'/width/400/height/330');
                },
                'params':{
                    'wmode': 'opaque'
                }
            }); 
        }else if(type == 'cl' || type == 'cr' || type == 'ct' || type == 'cb'){
            if(type == 'cl'){
                layout = 'left'; 
            } else if(type == 'cr'){
                layout = 'right';
            } else if(type == 'ct'){
                layout = 'top';
            } else if(type == 'cb'){
                layout = 'bottom';
            }
            
            flashvars.ppv = {
                "plugin" : true,
                "path" : "/p/"+pid+"/sp/"+pid+"00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                "relativeTo" : "video",
                "position" : "before",                    
                "onPageJs1" : "{onPagePluginPath}/ppv/ppv.js",
                "onPageJs2" : "{onPagePluginPath}/ppv/resources/js/jquery.validate.min.js",
                "onPageJs3" : "{onPagePluginPath}/ppv/resources/js/jstz.min.js",   
                "onPageJs4" : "{onPagePluginPath}/ppv/resources/js/bootstrap.min.js", 
                "onPageJs5" : "{onPagePluginPath}/libs/jcarousellite.js",
                "onPageJs6" : "{onPagePluginPath}/libs/jquery.sortElements.js",
                "onPageCss1" : "{onPagePluginPath}/ppv/resources/css/smh_ppv_style.css", 
                "onPageCss3" : "{onPagePluginPath}/ppv/resources/css/categoryOnPage.css",
                "requiresJQuery" : true,
                "pid" : pid,
                "sm_ak" : sm_ak,
                "uiConfId" : uiconf_id,
                "uiConf_width" : uiconf_width,
                "uiConf_height" : uiconf_height,
                "entryId" : entryId,
                "type" : type,
                "layoutMode" : layout
            }
            var sessData = {
                pid: pid,
                cat_id: entryId,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_get_cat_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function(data) {
                kWidget.thumbEmbed({
                    'targetId': kdpId,
                    'wid': '_'+pid,
                    'uiconf_id' : uiconf_id,
                    'entry_id' : data,
                    'width': uiconf_width,
                    'height': uiconf_height,
                    'flashvars': flashvars,
                    'thumbReadyCallback': function(){
                        ppv.setupPlayer(sm_ak,entryId,type);
                        window.pptransact.init(false);
                    },
                    'params':{
                        'wmode': 'opaque'
                    }
                }); 
            }); 
                      
        }
    }
}
        
function load_html5(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/mwEmbedLoader.php';
    jqTag.onload = load_smh_ppv;
    headTag.appendChild(jqTag);    
    $smh = jQuery.noConflict();
}
    
function load_cookies(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/js/jquery.cookie.js';
    jqTag.onload = load_html5;
    headTag.appendChild(jqTag); 
} 
    
function load_pptransact(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/js/pptransact.js';
    jqTag.onload = load_cookies;
    headTag.appendChild(jqTag);  
}
    
function load_jquery(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/resources/jquery/jquery.min.js';
    jqTag.onload = load_pptransact;
    headTag.appendChild(jqTag); 
}
    
var headTag = document.getElementsByTagName("head")[0];
var jqTag = document.createElement('script');
jqTag.type = 'text/javascript';
jqTag.src = ppv_protocol+'://www.paypalobjects.com/js/external/dg.js';
jqTag.onload = load_jquery;
headTag.appendChild(jqTag);           

ppv = new ppv_init();

window.addEventListener("message", function(event) {
    var action = JSON.parse(event.data);
    if(action['action'] == 'cancel'){
        pptransact.releaseDG();
        $smh('#smh_purchase_window').modal('hide');
    }
    
    if(action['paymentStatus'] != '' || action['paymentStatus'] != null){
        pptransact.releaseDG(action);
    }
});