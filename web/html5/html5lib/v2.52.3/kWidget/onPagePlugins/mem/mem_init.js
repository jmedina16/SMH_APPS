var mem_init = function () {
    return this.init();
}

mem_init.prototype = {
    init: function (p, vr, vr_format, delivery, fb_eid, sc_m) {
        if (vr === undefined)
            vr = false;
        if (vr_format === undefined)
            vr_format = '2d';
        if (delivery === undefined)
            delivery = 'hls';
        if (fb_eid === undefined)
            fb_eid = null;
        if (sc_m === undefined)
            sc_m = true;
        refresh_player = true;
        current_time = 0;
        is_logged_in = false;
        cat_entries = new Array();
        blocked = false;
        livestream = false;
        playlist = false;
        category = false;
        media_type = '';
        protocol = p;
        smh_vr = vr;
        smh_vr_format = vr_format;
        smh_vr_delivery = delivery;
        fallbackEntryID = fb_eid;
        showCompMessage = sc_m;
        player_width = 0;
        player_height = 0;
        login_entryid = '';
        is_active = '';
        active_interval_set = false;
        userId = '';
        entry_title = '';
        paused = true;
        fadeout_timer = 0;
        fadeout_timer_m = 0;
        owner_attr = new Array();
    },
    checkAccess: function (pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type) {
        $smh('#memWindow').css('display', 'none');
        var sessData;
        sessData = {
            entry_id: entryId,
            sm_ak: sm_ak
        }
        $smh.ajax({
            type: "GET",
            url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=setup_player",
            data: sessData,
            dataType: 'json'
        }).done(function (data) {
            entry_title = data['title'];
            if (data['ac_type'] == 1) {
                blocked = true;
            } else {
                blocked = false;
            }

            if (data['attrs']) {
                owner_attr = JSON.parse(data['attrs']);
            }

            media_type = data['media_type'];

            if (data['media_type'] == 100 || data['media_type'] == 101) {
                livestream = true;
            } else if (data['media_type'] == 3) {
                playlist = true;
            }

            if (type == 'cl' || type == 'cr' || type == 'ct' || type == 'cb') {
                category = true;

                sessData = {
                    cat_id: entryId,
                    sm_ak: sm_ak
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=get_cat_entries",
                    data: sessData,
                    dataType: 'json'
                }).done(function (data) {
                    cat_entries = data;
                });
            }

            var smh_sess = $smh.cookie('smh_auth_key');
            if (smh_sess == '' || smh_sess == undefined || smh_sess == null) {
                mem.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
            } else {
                sessData = {
                    auth_key: smh_sess,
                    pid: pid,
                    sm_ak: sm_ak,
                    type: type,
                    entryId: entryId
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=is_logged_in",
                    data: sessData,
                    dataType: 'json'
                }).done(function (data) {
                    if (data['success']) {
                        is_logged_in = true;
                        userId = data['user_id'];
                        mem.loadVideo(data['token'], pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                        if (!active_interval_set) {
                            mem.isActive(pid, sm_ak, data['user_id']);
                            is_active = setInterval(function () {
                                mem.isActive(pid, sm_ak, data['user_id']);
                            }, 600000);
                            active_interval_set = true;
                        }
                    } else {
                        mem.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                    }
                });
            }
        });
    },
    isActive: function (pid, sm_ak, userId) {
        var sessData = {
            uid: userId,
            pid: pid,
            sm_ak: sm_ak
        }
        $smh.ajax({
            type: "GET",
            url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=is_active",
            data: sessData,
            dataType: 'json'
        });
    },
    isNotActive: function (pid, sm_ak) {
        clearInterval(is_active);
        active_interval_set = false;
        var sessData = {
            uid: userId,
            pid: pid,
            sm_ak: sm_ak
        }
        $smh.ajax({
            type: "GET",
            url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=is_not_active",
            data: sessData,
            dataType: 'json'
        });
    },
    resizer: function (kdpId) {
        $smh('#memWindow').css('top', $smh('#' + kdpId).position().top);
        $smh('#memWindow').css('left', $smh('#' + kdpId).position().left);
    },
    startResize: function (kdpId) {
        $smh(window).resize(function () {
            mem.resizer(kdpId);
        });
    },
    endResize: function () {
        $smh(window).off("resize");
    },
    fadeLogout: function (kdpId, entryId) {
        $smh('#memWindow').css('top', $smh('#' + kdpId).position().top);
        $smh('#memWindow').css('left', $smh('#' + kdpId).position().left);
        $smh('#memWindow').css('width', parseInt($smh('#' + kdpId).css('width')));
        $smh('#memWindow').css('height', '40px');
        $smh('#memWindow').show();
        $smh('#memWindow').html('<div class="topBarContainer hover open"><button class="btn pull-left display-medium tooltipBelow" title="Profile" onclick="mem_obj.smhProfile();"><i class="fa fa-user"></i><span class="accessibilityLabel">Profile</span></button><button class="btn pull-right display-medium tooltipBelow" title="Logout" onclick="mem_obj.smhLogout(\'' + entryId + '\');"><span class="accessibilityLabel">Logout</span><i class="fa fa-sign-out"></i></button></div>');
        mem.endResize();
        mem.startResize(kdpId);
        fadeout_timer_m = null;
        $smh(window).on('touchmove', function () {
            clearTimeout(fadeout_timer_m);
            $smh('#memWindow').fadeIn();
            fadeout_timer_m = setTimeout('$smh("#memWindow").fadeOut("slow", function(){ mem.showButton(); });', 3000);
        });

        fadeout_timer = null;
        if (!paused) {
            fadeout_timer = setTimeout('$smh("#memWindow").fadeOut("slow", function(){ mem.showButton(); });', 3000);
        }
        $smh(document).on('mousemove', '#' + kdpId + ',#memWindow', function () {
            clearTimeout(fadeout_timer);
            $smh('#memWindow').fadeIn();
            if (!paused) {
                fadeout_timer = setTimeout('$smh("#memWindow").fadeOut("slow", function(){ mem.showButton(); });', 3000);
            }
        });
    },
    showButton: function () {
        if (blocked) {
            if (!is_logged_in) {
                $smh('#memWindow').show();
            }
        }
    },
    loadWall: function (kdpId, pid, entryId, thumbId, uiconf_width, uiconf_height) {
        var logged_in_content = '';
        var button_id = '';
        var free_preview = '';
        var window_title = '';
        if (!blocked) {
            free_preview = '<div id="fp-wrapper">' +
                    '<a onclick="mem.watchPreview(\'' + kdpId + '\',\'' + thumbId + '\');"><i class="fa fa-play-circle"></i><span>watch preview</span></a>' +
                    '</div>';
        }
        if (!is_logged_in) {
            $smh('#memWindow').css('top', $smh('#' + kdpId).position().top);
            $smh('#memWindow').css('left', $smh('#' + kdpId).position().left);
            $smh('#memWindow').css('width', parseInt($smh('#' + kdpId).css('width')));
            if (smh_vr && showCompMessage) {
                $smh('#memWindow').css('height', parseInt($smh('#' + kdpId).css('height')) - 70);
            } else {
                $smh('#memWindow').css('height', parseInt($smh('#' + kdpId).css('height')));
            }
            if (playlist) {
                if (uiconf_height > uiconf_width) {
                    button_id = 'smh_vplaylist_button_wrapper';
                } else {
                    button_id = 'smh_button_wrapper';
                }
            } else {
                button_id = 'smh_button_wrapper';
            }
            if (livestream) {
                window_title = 'live stream';
            } else if (playlist) {
                window_title = 'playlist';
            } else {
                window_title = 'video';
            }
            logged_in_content = '<div id="register-text">' +
                    'You must have an account to view this ' + window_title + '.<br> Don\'t have an account? <a onclick="mem_obj.register_window();">Register Here</a>' +
                    '</div>' +
                    '<div class="clear"></div>';

            $smh('#memWindow').show();
            $smh('#memWindow').html('<div id="' + button_id + '">' +
                    '<div id="header">' +
                    '<i class="fa fa-lock"></i> <div id="title">' + entry_title + '</div>' +
                    '</div>' +
                    '<div id="content-wrapper">' +
                    '<div class="wrap">' +
                    '<div id="entry-thumb" class="column">' +
                    '<img width="130px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/p/' + pid + '/thumbnail/entry_id/' + thumbId + '/width/130">' +
                    free_preview +
                    '</div>' +
                    '<div id="purchase-ticket" class="column">' +
                    '<button onclick="loginHandler()" type="button" class="buyButton" id="buyNowButton">Login</button>' +
                    '</div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    logged_in_content +
                    '</div>' +
                    '</div>');
            mem.startResize(kdpId);
        } else {
            mem.fadeLogout(kdpId, entryId);
        }
    },
    watchPreview: function (kdpId, thumbId) {
        var entry_id = mem_obj.getConfig('entryId');
        if (smh_vr) {
            kdp.sendNotification("doSeek", 0);
            window.kdp.sendNotification('doPlay');
            paused = false;
            $smh('#memWindow').hide();
            if (is_logged_in) {
                mem.fadeLogout(kdpId, entry_id);
            }
        } else {
            window.kdp.sendNotification('cleanMedia');
            if (playlist || category) {
                window.kdp.sendNotification('changeMedia', {
                    'entryId': thumbId
                });
            } else {
                window.kdp.sendNotification('changeMedia', {
                    'entryId': entry_id
                });
            }
            window.kdp.sendNotification('doPlay');
//            setTimeout(function () {
//                window.kdp.sendNotification('doPlay');
//            }, 1500);
            paused = false;
            $smh('#memWindow').hide();
            if (is_logged_in) {
                mem.fadeLogout(kdpId, entry_id);
            }
        }
    },
    loadVideo: function (ks, pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type) {
        $smh('#memWindow').empty();
        $smh('#memWindow').css('display', 'none');
        mem.endResize();
        if (window.kdp) {
            kWidget.destroy(window.kdp);
            delete(window.kdp);
        }
        var uniqid = +new Date();
        var kdpId = 'smhtarget' + uniqid;

        $smh('#myVideoContainer').html(
                '<div id="' + kdpId + '" style="width:400px;height:330px"></div>'
                );
        mw.setConfig('Kaltura.LeadWithHTML5', true);
        flashvars = {};
        flashvars.externalInterfaceDisabled = false;
        flashvars.autoPlay = false;
        flashvars.disableAlerts = true;
        flashvars.httpProtocol = protocol;
        if (ks != "")
            flashvars.ks = ks;

        if (smh_vr) {
            flashvars.streamerType = 'http';
            flashvars['mediaProxy.preferedFlavorBR'] = 5000;
            if (smh_vr_format == '2d') {
                flashvars.smhVR = {
                    "plugin": true,
                    "iframeHTML5Js1": "{onPagePluginPath}/smhVR/js/vrIframeAddin2D.js",
                    "fallbackEntryID": fallbackEntryID,
                    "showCompMessage": showCompMessage
                }
            } else if (smh_vr_format == 'sbs') {
                flashvars.smhVR = {
                    "plugin": true,
                    "iframeHTML5Js1": "{onPagePluginPath}/smhVR/js/vrIframeAddinSBS.js",
                    "fallbackEntryID": fallbackEntryID,
                    "showCompMessage": showCompMessage
                }
            } else if (smh_vr_format == 'tb') {
                flashvars.smhVR = {
                    "plugin": true,
                    "iframeHTML5Js1": "{onPagePluginPath}/smhVR/js/vrIframeAddinTB.js",
                    "fallbackEntryID": fallbackEntryID,
                    "showCompMessage": showCompMessage
                }
            }

            if (smh_vr_delivery == 'hls') {
                flashvars.LeadHLSOnAndroid = true;
                flashvars.LeadWithHLSOnJs = true;
                flashvars.hlsjs = {"plugin": true}
            }

            flashvars.controlBarContainer = {
                "plugin": true,
                "hover": false
            }
            flashvars.sourceSelector = {
                "plugin": true,
                "switchOnResize": false,
                "simpleFormat": true,
                "displayMode": "sizebitrate"
            }
            flashvars.smhVRBtn = {
                'plugin': true
            }
        } else {
            flashvars.streamerType = 'rtmp';
        }

        player_width = uiconf_width;
        player_height = uiconf_height
        login_entryid = entryId;
        if (type == 's') {
            flashvars.entryId = entryId;
            flashvars.mem = {
                "plugin": true,
                "path": "/p/" + pid + "/sp/" + pid + "00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                "relativeTo": "video",
                "position": "before",
                "pid": pid,
                "sm_ak": sm_ak,
                "uiConfId": uiconf_id,
                "uiConf_width": uiconf_width,
                "uiConf_height": uiconf_height,
                "entryId": entryId,
                "type": type
            }
            kWidget.embed({
                'targetId': kdpId,
                'wid': '_' + pid,
                'uiconf_id': uiconf_id,
                'entry_id': entryId,
                'width': uiconf_width,
                'height': uiconf_height,
                "cache_st": 1422674704,
                'flashvars': flashvars,
                readyCallback: function () {
                    mem.loadWall(kdpId, pid, entryId, entryId, uiconf_width, uiconf_height);
                },
                'params': {
                    'wmode': 'opaque'
                }
            });
        } else if (type == 'p') {
            flashvars.mem = {
                "plugin": true,
                "path": "/p/" + pid + "/sp/" + pid + "00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                "relativeTo": "video",
                "position": "before",
                "pid": pid,
                "sm_ak": sm_ak,
                "uiConfId": uiconf_id,
                "uiConf_width": uiconf_width,
                "uiConf_height": uiconf_height,
                "entryId": entryId,
                "type": type
            }

            flashvars['playlistAPI.includeInLayout'] = true;
            flashvars['playlistAPI.autoPlay'] = false;
            flashvars['playlistAPI.autoContinue'] = true;
            flashvars['playlistAPI.autoInsert'] = true;
            flashvars['playlistAPI.kpl0Id'] = entryId;

            if (uiconf_height > uiconf_width) {
                flashvars['playlistAPI.containerPosition'] = "bottom";
            }

            var sessData = {
                pid: pid,
                entry_id: entryId,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=w_get_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                kWidget.embed({
                    'targetId': kdpId,
                    'wid': '_' + pid,
                    'uiconf_id': uiconf_id,
                    'width': uiconf_width,
                    'height': uiconf_height,
                    'flashvars': flashvars,
                    readyCallback: function () {
                        mem.loadWall(kdpId, pid, entryId, data, uiconf_width, uiconf_height);
                    },
                    'params': {
                        'wmode': 'opaque'
                    }
                });
            });
        } else if (type == 'cl' || type == 'cr' || type == 'ct' || type == 'cb') {
            if (type == 'cl') {
                layout = 'left';
            } else if (type == 'cr') {
                layout = 'right';
            } else if (type == 'ct') {
                layout = 'top';
            } else if (type == 'cb') {
                layout = 'bottom';
            }

            flashvars.mem = {
                "plugin": true,
                "path": "/p/" + pid + "/sp/" + pid + "00/flash/kdp3/v3.8_hds/plugins/facadePlugin.swf",
                "relativeTo": "video",
                "position": "before",
                "pid": pid,
                "sm_ak": sm_ak,
                "uiConfId": uiconf_id,
                "uiConf_width": uiconf_width,
                "uiConf_height": uiconf_height,
                "entryId": entryId,
                "type": type,
                "layoutMode": layout
            }
            var sessData = {
                pid: pid,
                cat_id: entryId,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=w_get_cat_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                kWidget.embed({
                    'targetId': kdpId,
                    'wid': '_' + pid,
                    'uiconf_id': uiconf_id,
                    'entry_id': data,
                    'width': uiconf_width,
                    'height': uiconf_height,
                    'flashvars': flashvars,
                    readyCallback: function () {
                        mem.loadWall(kdpId, pid, entryId, data, uiconf_width, uiconf_height);
                    },
                    'params': {
                        'wmode': 'opaque'
                    }
                });
            });
        }
    }
}

function load_Css5() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('link');
    jqTag.setAttribute("rel", "stylesheet");
    jqTag.setAttribute("type", "text/css");
    jqTag.setAttribute("href", mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/smh_mem_style.css?v=1.6')
    jqTag.onload = load_smh_mem;
    headTag.appendChild(jqTag);
}

function load_Css4() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('link');
    jqTag.setAttribute("rel", "stylesheet");
    jqTag.setAttribute("type", "text/css");
    jqTag.setAttribute("href", mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/tooltipster.css')
    jqTag.onload = load_Css5;
    headTag.appendChild(jqTag);
}

function load_Css3() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('link');
    jqTag.setAttribute("rel", "stylesheet");
    jqTag.setAttribute("type", "text/css");
    jqTag.setAttribute("href", mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/jquery.dataTables.min.css?v=1.6')
    jqTag.onload = load_Css4;
    headTag.appendChild(jqTag);
}

function load_Css2() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('link');
    jqTag.setAttribute("rel", "stylesheet");
    jqTag.setAttribute("type", "text/css");
    jqTag.setAttribute("href", mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/font-awesome.min.css')
    jqTag.onload = load_Css3;
    headTag.appendChild(jqTag);
}

function load_Css1() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('link');
    jqTag.setAttribute("rel", "stylesheet");
    jqTag.setAttribute("type", "text/css");
    jqTag.setAttribute("href", mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/css/categoryOnPage.css')
    jqTag.onload = load_Css2;
    headTag.appendChild(jqTag);
}

function load_Js7() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/libs/jquery.sortElements.js';
    jqTag.onload = load_Css1;
    headTag.appendChild(jqTag);
}

function load_Js6() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/libs/jcarousellite.js';
    jqTag.onload = load_Js7;
    headTag.appendChild(jqTag);
}

function load_Js5() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/js/bootstrap.min.js';
    if (mem_type == 'cl' || mem_type == 'cr' || mem_type == 'ct' || mem_type == 'cb') {
        jqTag.onload = load_Js6;
    } else {
        jqTag.onload = load_Css2;
    }
    headTag.appendChild(jqTag);
}

function load_Js4() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/js/jquery.tooltipster.min.js';
    jqTag.onload = load_Js5;
    headTag.appendChild(jqTag);
}

function load_Js3() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/js/jstz.min.js';
    jqTag.onload = load_Js4;
    headTag.appendChild(jqTag);

    $smh.validator.addMethod('mypassword', function (value, element) {
        return this.optional(element) || (value.match(/[A-Z]/) && value.match(/[a-z]/) && value.match(/[0-9]/));
    },
            'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
}

function load_Js2() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/js/jquery.validate.min.js';
    jqTag.onload = load_Js3;
    headTag.appendChild(jqTag);
}

function load_Js1() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/mem.js?v=1.6';
    jqTag.onload = load_Js2;
    headTag.appendChild(jqTag);
}

function load_html5() {
    $smh = jQuery.noConflict();
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/mwEmbedLoader.php';
    jqTag.onload = load_Js1;
    headTag.appendChild(jqTag);
}

function load_cookies() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/js/jquery.cookie.js';
    jqTag.onload = load_html5;
    headTag.appendChild(jqTag);
}

function load_datatables() {
    var headTag = document.getElementsByTagName("head")[0];
    var jqTag = document.createElement('script');
    jqTag.type = 'text/javascript';
    jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/js/jquery.dataTables.js?v=1.6';
    jqTag.onload = load_cookies;
    headTag.appendChild(jqTag);
}

var headTag = document.getElementsByTagName("head")[0];
var jqTag = document.createElement('script');
jqTag.type = 'text/javascript';
jqTag.src = mem_protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/resources/jquery/jquery.min.js';
jqTag.onload = load_datatables;
headTag.appendChild(jqTag);

mem = new mem_init();