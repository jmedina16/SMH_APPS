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
        player_width = 0;
        player_height = 0;
        login_entryid = '';
        is_active = '';
        active_interval_set = false;
    },
    checkAccess: function(pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type){
        $smh('#purchaseWindow').css('display','none');
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
            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=setup_player",
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
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=get_cat_entries",
                    data: sessData,
                    dataType: 'json'
                }).done(function(data) {
                    cat_entries = data;
                });
            }   
            
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
                        url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=is_logged_in",
                        data: sessData,
                        dataType: 'json'
                    }).done(function(data) {
                        if(data['success']){
                            is_logged_in = true;
                            userId = data['user_id'];
                            ppv.checkInventory(pid,sm_ak,data['user_id'],uiconf_id,uiconf_width,uiconf_height,entryId,type);
                            if(!active_interval_set){
                                ppv.isActive(pid,sm_ak);
                                is_active = setInterval( function(){
                                    ppv.isActive(pid,sm_ak);
                                }, 600000 );
                                active_interval_set = true;
                            }
                        } else {
                            ppv.loadVideo('',pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
                        }                       
                    });                   
                }   
            }
        }); 
    },
    isActive: function(pid,sm_ak){
        var sessData = {
            uid: userId,
            pid: pid,
            sm_ak: sm_ak
        }
        $smh.ajax({
            type: "GET",
            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=is_active",
            data: sessData,
            dataType: 'json'
        });
    },
    isNotActive: function(pid,sm_ak){
        clearInterval(is_active);
        active_interval_set = false;
        var sessData = {
            uid: userId,
            pid: pid,
            sm_ak: sm_ak
        }
        $smh.ajax({
            type: "GET",
            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=is_not_active",
            data: sessData,
            dataType: 'json'
        });
    },
    checkInventory: function(pid,sm_ak,uid,uiconf_id,uiconf_width,uiconf_height,entryId,type){
        var timezone = jstz.determine();
        var sessData = {
            entryId: entryId,
            uid: uid,
            pid: pid,
            sm_ak: sm_ak,
            type: type,
            tz: timezone.name()
        }
        $smh.ajax({
            type: "GET",
            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=check_inventory",
            data: sessData,
            dataType: 'json'
        }).done(function(data) {
            if(!data){
                ppv.loadVideo('',pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
            } else {
                paid = true;
                ppv.loadVideo(data,pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);                  
            }                                
        }); 
    },
    loadVideo: function(ks,pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type){
        $smh('#ppv-wrapper').css('width',uiconf_width);
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
        flashvars.autoPlay = false;
        flashvars.disableAlerts = true;
        flashvars.streamerType = 'rtmp';      
        flashvars.httpProtocol = protocol;
        if(ks != "") flashvars.ks = ks; 
        
        player_width = uiconf_width;
        player_height = uiconf_height
        login_entryid = entryId;
            
        if(type == 's'){
            flashvars.entryId = entryId;
            flashvars.ppv = {
                "plugin" : true, 
                "path" : "/p/"+pid+"/sp/"+pid+"00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                "relativeTo" : "video",
                "position" : "before", 
                "pid" : pid,
                "sm_ak" : sm_ak,
                "uiConfId" : uiconf_id,
                "uiConf_width" : uiconf_width,
                "uiConf_height" : uiconf_height,
                "entryId" : entryId,
                "type" : type
            }
            kWidget.embed({
                'targetId': kdpId,
                'wid': '_'+pid,
                'uiconf_id' : uiconf_id,
                'entry_id' : entryId,
                'width': uiconf_width,
                'height': uiconf_height,
                "cache_st": 1422674704,
                'flashvars': flashvars,
                readyCallback: function(){
                    if(blocked){
                        if(!paid){
                            $smh('#purchaseWindow').css('top', $smh('#'+kdpId).position().top);
                            $smh('#purchaseWindow').css('left', $smh('#'+kdpId).position().left);
                            $smh('#purchaseWindow').css('width', parseInt($smh('#'+kdpId).css('width')));
                            $smh('#purchaseWindow').css('height', parseInt($smh('#'+kdpId).css('height'))); 
                            var button_id = '';
                            if(playlist){
                                if(player_height > player_width){
                                    button_id = 'smh_button_wrapper';                                
                                } else {
                                    button_id = 'smh_hplaylist_button_wrapper';
                                }  
                            } else {
                                button_id = 'smh_button_wrapper';
                            }      
                            var purchased_button = '';
                            if(!is_logged_in){
                                purchased_button = '<button onclick="loginHandler()" type="button" class="purchasedButton" id="purchasedButton">Already Purchased?</button>';
                            }
                            $smh('#purchaseWindow').show();
                            $smh('#purchaseWindow').html('<div id="'+button_id+'"><button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton">Buy Now</button>'+purchased_button+'</div>'); 
                        
                            window.onresize = function() {
                                $smh('#purchaseWindow').css('top', $smh('#'+kdpId).position().top);
                                $smh('#purchaseWindow').css('left', $smh('#'+kdpId).position().left);
                            };     
                        }          
                    } 
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
                "pid" : pid,
                "sm_ak" : sm_ak,
                "uiConfId" : uiconf_id,
                "uiConf_width" : uiconf_width,
                "uiConf_height" : uiconf_height,
                "entryId" : entryId,
                "type" : type
            }
            
            flashvars['playlistAPI.includeInLayout'] = true;
            flashvars['playlistAPI.autoPlay'] = false;
            flashvars['playlistAPI.autoContinue'] = true;
            flashvars['playlistAPI.autoInsert'] = true;
            flashvars['playlistAPI.kpl0Id'] = entryId;
        
            if(uiconf_height > uiconf_width){
                flashvars['playlistAPI.containerPosition'] = "bottom";
            }

            kWidget.embed({
                'targetId': kdpId,
                'wid': '_'+pid,
                'uiconf_id' : uiconf_id,
                'width': uiconf_width,
                'height': uiconf_height,
                'flashvars': flashvars,
                readyCallback: function(){
                    if(blocked){
                        if(!paid){
                            $smh('#purchaseWindow').css('top', $smh('#'+kdpId).position().top);
                            $smh('#purchaseWindow').css('left', $smh('#'+kdpId).position().left);
                            $smh('#purchaseWindow').css('width', parseInt($smh('#'+kdpId).css('width')));
                            $smh('#purchaseWindow').css('height', parseInt($smh('#'+kdpId).css('height'))); 
                            var button_id = '';
                            if(playlist){
                                if(player_height > player_width){
                                    button_id = 'smh_button_wrapper';                                
                                } else {
                                    button_id = 'smh_hplaylist_button_wrapper';
                                }  
                            } else {
                                button_id = 'smh_button_wrapper';
                            }      
                            var purchased_button = '';
                            if(!is_logged_in){
                                purchased_button = '<button onclick="loginHandler()" type="button" class="purchasedButton" id="purchasedButton">Already Purchased?</button>';
                            }
                            $smh('#purchaseWindow').show();
                            $smh('#purchaseWindow').html('<div id="'+button_id+'"><button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton">Buy Now</button>'+purchased_button+'</div>'); 
                        
                            window.onresize = function() {
                                $smh('#purchaseWindow').css('top', $smh('#'+kdpId).position().top);
                                $smh('#purchaseWindow').css('left', $smh('#'+kdpId).position().left);
                            };     
                        }          
                    } 
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
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=w_get_cat_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function(data) {
                kWidget.embed({
                    'targetId': kdpId,
                    'wid': '_'+pid,
                    'uiconf_id' : uiconf_id,
                    'entry_id' : data,
                    'width': uiconf_width,
                    'height': uiconf_height,
                    'flashvars': flashvars,
                    readyCallback: function(){
                        if(blocked){
                            if(!paid){
                                $smh('#purchaseWindow').css('top', $smh('#'+kdpId).position().top);
                                $smh('#purchaseWindow').css('left', $smh('#'+kdpId).position().left);
                                $smh('#purchaseWindow').css('width', parseInt($smh('#'+kdpId).css('width')));
                                $smh('#purchaseWindow').css('height', parseInt($smh('#'+kdpId).css('height'))); 
                                var button_id = '';
                                if(playlist){
                                    if(player_height > player_width){
                                        button_id = 'smh_button_wrapper';                                
                                    } else {
                                        button_id = 'smh_hplaylist_button_wrapper';
                                    }  
                                } else {
                                    button_id = 'smh_button_wrapper';
                                }      
                                var purchased_button = '';
                                if(!is_logged_in){
                                    purchased_button = '<button onclick="loginHandler()" type="button" class="purchasedButton" id="purchasedButton">Already Purchased?</button>';
                                }
                                $smh('#purchaseWindow').show();
                                $smh('#purchaseWindow').html('<div id="'+button_id+'"><button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton">Buy Now</button>'+purchased_button+'</div>'); 
                        
                                window.onresize = function() {
                                    $smh('#purchaseWindow').css('top', $smh('#'+kdpId).position().top);
                                    $smh('#purchaseWindow').css('left', $smh('#'+kdpId).position().left);
                                };     
                            }          
                        } 
                    },
                    'params':{
                        'wmode': 'opaque'
                    }
                }); 
            });                                    
        }
    }
}

function load_Js7(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/libs/jquery.sortElements.js';
    jqTag.onload = load_smh_ppv;
    headTag.appendChild(jqTag);  
}

function load_Js6(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/libs/jcarousellite.js';
    jqTag.onload = load_Js7;
    headTag.appendChild(jqTag);  
}

function load_Js5(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/bootstrap.min.js';
    if(ppv_type == 'cl' || ppv_type == 'cr' || ppv_type == 'ct' || ppv_type == 'cb'){
        jqTag.onload = load_Js6;
    } else {
        jqTag.onload = load_smh_ppv; 
    }    
    headTag.appendChild(jqTag);  
}

function load_Js4(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.tooltipster.min.js';
    jqTag.onload = load_Js5;
    headTag.appendChild(jqTag);  
}

function load_Js3(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/jstz.min.js';
    jqTag.onload = load_Js4;
    headTag.appendChild(jqTag);  
    
    $smh.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[A-Z]/) && value.match(/[a-z]/) && value.match(/[0-9]/));
    },
    'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
}

function load_Js2(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.validate.min.js';
    jqTag.onload = load_Js3;
    headTag.appendChild(jqTag);  
}

function load_Js1(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/ppv.js';
    jqTag.onload = load_Js2;
    headTag.appendChild(jqTag);  
}

function load_html5(){
    $smh = jQuery.noConflict();
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/mwEmbedLoader.php';
    jqTag.onload = load_Js1;
    headTag.appendChild(jqTag);    
}

function load_cookies(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.cookie.js';
    jqTag.onload = load_html5;
    headTag.appendChild(jqTag); 
} 
    
function load_pptransact(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/pptransact.js';
    jqTag.onload = load_cookies;
    headTag.appendChild(jqTag);  
}
    
function load_jquery(){
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = ppv_protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.24/resources/jquery/jquery.min.js';
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
    try{
        var action = JSON.parse(event.data);
        if(action['action'] == 'cancel'){
            pptransact.releaseDG();
            $smh('#smh_purchase_window').modal('hide');
            var pid = ppv_obj.getConfig('pid');
            var entryId = ppv_obj.getConfig('entryId');
            var type = ppv_obj.getConfig('type');
            var sm_ak = ppv_obj.getConfig('sm_ak');
            var uiconf_id = ppv_obj.getConfig('uiConfId');
            var uiconf_width = ppv_obj.getConfig('uiConf_width');
            var uiconf_height = ppv_obj.getConfig('uiConf_height');
            ppv.checkAccess(pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
        }      
        if(action['paymentStatus'] != '' || action['paymentStatus'] != null){
            pptransact.releaseDG(action);
        } 
    }
    catch(e){
        console.log('SMH DEBUG: Invalid JSON');
    }  
});