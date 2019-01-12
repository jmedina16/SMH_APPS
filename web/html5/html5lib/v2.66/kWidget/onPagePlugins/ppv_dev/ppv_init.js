function ppv_init() {
    window.smh = '';
}

ppv_init.prototype = {
    constructor: ppv_init,
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
        is_logged_in = false;
        init_loaded = false;
        userId = 0;
        smh_ppv_order = new Array();
        cat_entries = new Array();
        blocked = false;
        livestream = false;
        paid = false;
        playlist = false;
        category = false;
        media_type = '';
        smh_aff = '';
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
        cat_assets_loaded = false;
        pp_assets_loaded = false;
        gw_type = 0;
        entry_title = '';
        paused = true;
        fadeout_timer = 0;
        fadeout_timer_m = 0;
        start_date = null;
        end_date = null;
        scheduled_is_before = false;
        scheduled_is_after = false;
        scheduled_has_start_date = false;
        scheduled_has_end_date = false;
        countdown = false;
        timezone = null;
        owner_attr = new Array();
    },
    checkAccess: function (pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type) {
        ppv.loadBaseAssets(pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
    },
    setup: function (pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type) {
        window.smh('#purchaseWindow').css('display', 'none');
        var smh_aff_cookie = window.smh.cookie('smh_aff');
        if (smh_aff_cookie != '' && smh_aff_cookie != undefined && smh_aff_cookie != null) {
            smh_aff = smh_aff_cookie;
        } else {
            if (ppv.$_GET('smh_aff')) {
                var smh_exp = Number(ppv.$_GET('smh_exp'));
                window.smh.cookie('smh_aff', ppv.$_GET('smh_aff'), {
                    expires: smh_exp,
                    path: '/'
                });
                smh_aff = ppv.$_GET('smh_aff');
            }
        }

        var sessData = {
            entry_id: entryId,
            sm_ak: sm_ak
        }
        window.smh.ajax({
            type: "GET",
            url: protocol + "://api.mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=setup_player",
            data: sessData,
            dataType: 'json'
        }).done(function (data) {
            entry_title = data['title'];
            gw_type = data['gw_type'];

            if (data['ac_type'] == 1) {
                blocked = true;
            } else {
                blocked = false;
            }

            if (data['attrs']) {
                owner_attr = JSON.parse(data['attrs']);
            }

            if (data['start_date'] || data['end_date']) {
                var current_epoch_date = (new Date).getTime();
                var current_date = moment.tz(current_epoch_date, user_timezone_name);
                if (data['start_date'] && !data['end_date']) {
                    countdown = (data['countdown'] == 'true') ? true : false;
                    timezone = data['timezone'];
                    scheduled_has_start_date = true;
                    start_date = data['start_date'] * 1000;
                    var scheduled_start_date = moment.tz(start_date, user_timezone_name);
                    scheduled_is_before = (current_date.isBefore(scheduled_start_date)) ? true : false;
                }
                if (data['start_date'] && data['end_date']) {
                    countdown = (data['countdown'] == 'true') ? true : false;
                    timezone = data['timezone'];
                    scheduled_has_start_date = true;
                    scheduled_has_end_date = true;
                    start_date = data['start_date'] * 1000;
                    end_date = data['end_date'] * 1000;
                    var scheduled_start_date = moment.tz(start_date, user_timezone_name);
                    var scheduled_end_date = moment.tz(end_date, user_timezone_name);
                    scheduled_is_before = (current_date.isBefore(scheduled_start_date)) ? true : false;
                    scheduled_is_after = (current_date.isAfter(scheduled_end_date)) ? true : false;
                }
            }

            media_type = data['media_type'];

            if (data['media_type'] == 100 || data['media_type'] == 101) {
                livestream = true;
            } else if (data['media_type'] == 3) {
                playlist = true;
            }

            if (type == 'cl' || type == 'cr' || type == 'ct' || type == 'cb') {
                category = true;
                ppv.loadCatAssets(pid);
                var sessData = {
                    cat_id: entryId,
                    sm_ak: sm_ak
                }
                window.smh.ajax({
                    type: "GET",
                    url: protocol + "://api.mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=get_cat_entries",
                    data: sessData,
                    dataType: 'json'
                }).done(function (data) {
                    cat_entries = data;
                });
            }

            var smh_sess = window.smh.cookie('smh_auth_key');
            if (smh_sess == '' || smh_sess == undefined || smh_sess == null) {
                ppv.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
            } else {
                var sessData = {
                    auth_key: smh_sess,
                    pid: pid,
                    sm_ak: sm_ak
                }
                window.smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=is_logged_in",
                    data: sessData,
                    dataType: 'json'
                }).done(function (data) {
                    if (data['success']) {
                        is_logged_in = true;
                        userId = data['user_id'];
                        ppv.checkInventory(pid, sm_ak, data['user_id'], uiconf_id, uiconf_width, uiconf_height, entryId, type);
                        if (!active_interval_set) {
                            ppv.isActive(pid, sm_ak);
                            is_active = setInterval(function () {
                                ppv.isActive(pid, sm_ak);
                            }, 600000);
                            active_interval_set = true;
                        }
                    } else {
                        ppv.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                    }
                });
            }
        });
    },
    $_GET: function (param) {
        var vars = {};
        window.location.href.replace(location.hash, '').replace(
                /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
                function (m, key, value) { // callback
                    vars[key] = value !== undefined ? value : '';
                }
        );
        if (param) {
            return vars[param] ? vars[param] : null;
        }
        return vars;
    },
    loadBaseAssets: function (pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type) {
        // An array of scripts you want to load in order
        var scriptLibrary = [];
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/resources/jquery/jquery.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.dataTables.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.cookie.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/sp/' + pid + '00/embedIframeJs/uiconf_id/' + uiconf_id + '/partner_id/' + pid, 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/ppv.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.validate.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/jstz.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.tooltipster.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/moment.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/moment-timezone-with-data.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.countdown.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/js/bootstrap.min.js?v=1.6', 'script', 'text/javascript'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/css/font-awesome.min.css?v=1.6', 'link', 'text/css'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/css/jquery.dataTables.min.css?v=1.6', 'link', 'text/css'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/css/tooltipster.css?v=1.6', 'link', 'text/css'));
        scriptLibrary.push(new Array(ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/css/smh_ppv_style.css?v=1.6', 'link', 'text/css'));

        ppv.loadJsFilesSequentially(scriptLibrary, 0, function () {
            window.onload = function () {
                $(document).ready(function () {
                    window.smh = window.jQuery.noConflict();
                    window.smh.validator.addMethod('mypassword', function (value, element) {
                        return this.optional(element) || (value.match(/[A-Z]/) && value.match(/[a-z]/) && value.match(/[0-9]/));
                    }, 'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
                    user_timezone = jstz.determine();
                    user_timezone_name = user_timezone.name();
                    window.smh(document).on("keypress", "#smh-register-form", function (event) {
                        if (event.which == 13 && !event.shiftKey) {
                            if (window.smh('#register-tab').length > 0) {
                                ppv_obj.register(true);
                            } else {
                                ppv_obj.register(false);
                            }
                        }
                    });
                    window.smh(document).on("keypress", ".tab-content #smh-login-form", function (event) {
                        if (event.which == 13 && !event.shiftKey) {
                            ppv_obj.smhLogin(true);
                        }
                    });
                    window.smh(document).on("keypress", "#form-wrapper #smh-login-form", function (event) {
                        if (event.which == 13 && !event.shiftKey) {
                            ppv_obj.smhLogin(false);
                        }
                    });
                    window.smh(document).on("keypress", "#smh-password-form", function (event) {
                        if (event.which == 13 && !event.shiftKey) {
                            ppv_obj.pass_reset_form();
                        }
                    });
                    window.smh('body').append('<div class="modal fade" id="smh_purchase_window" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
                            '<div class="modal-dialog">' +
                            '<div class="modal-content">' +
                            '<div class="modal-header"></div>' +
                            '<div class="modal-body"></div>' +
                            '</div>' +
                            '</div>' +
                            '</div>');
                    ppv.setup(pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                });
            }
        });
    },
    loadJsFilesSequentially: function (scriptsCollection, startIndex, librariesLoadedCallback) {
        if (scriptsCollection[startIndex]) {
            var fileref = document.createElement(scriptsCollection[startIndex][1]);
            fileref.setAttribute("type", scriptsCollection[startIndex][2]);
            if (scriptsCollection[startIndex][2] === 'text/css') {
                fileref.setAttribute("href", scriptsCollection[startIndex][0]);
                fileref.setAttribute("rel", "stylesheet");
            } else {
                fileref.setAttribute("src", scriptsCollection[startIndex][0]);
            }
            fileref.onload = function () {
                startIndex = startIndex + 1;
                ppv.loadJsFilesSequentially(scriptsCollection, startIndex, librariesLoadedCallback)
            };
            document.getElementsByTagName("head")[0].appendChild(fileref)
        } else {
            librariesLoadedCallback();
        }
    },
    loadCatAssets: function (pid) {
        if (!cat_assets_loaded) {
            ppv.loadCarouselJs(pid);
            cat_assets_loaded = true;
        }
    },
    loadCarouselJs: function (pid) {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('script');
        jqTag.type = 'text/javascript';
        jqTag.src = ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/libs/jcarousellite.js?v=1.6';
        jqTag.onload = ppv.loadSortJs(pid);
        headTag.appendChild(jqTag);
    },
    loadSortJs: function (pid) {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('script');
        jqTag.type = 'text/javascript';
        jqTag.src = ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/libs/jquery.sortElements.js?v=1.6';
        jqTag.onload = ppv.loadCatCss(pid);
        headTag.appendChild(jqTag);
    },
    loadCatCss: function (pid) {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('link');
        jqTag.setAttribute("rel", "stylesheet");
        jqTag.setAttribute("type", "text/css")
        jqTag.setAttribute("href", ppv_protocol + '://apps.mediaplatform.streamingmediahosting.com/p/' + pid + '/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/css/categoryOnPage.css?v=1.6');
        headTag.appendChild(jqTag);
    },
    isActive: function (pid, sm_ak) {
        var sessData = {
            uid: userId,
            pid: pid,
            sm_ak: sm_ak
        }
        window.smh.ajax({
            type: "GET",
            url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=is_active",
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
        window.smh.ajax({
            type: "GET",
            url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=is_not_active",
            data: sessData,
            dataType: 'json'
        });
    },
    checkInventory: function (pid, sm_ak, uid, uiconf_id, uiconf_width, uiconf_height, entryId, type) {
        var sessData = {
            entryId: entryId,
            uid: uid,
            pid: pid,
            sm_ak: sm_ak,
            type: type,
            tz: user_timezone_name
        }
        window.smh.ajax({
            type: "GET",
            url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=check_inventory",
            data: sessData,
            dataType: 'json'
        }).done(function (data) {
            if (!data) {
                ppv.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
            } else {
                paid = true;
                ppv.loadVideo(data, pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
            }
        });
    },
    resizer: function (kdpId) {
        window.smh('#purchaseWindow').css('top', window.smh('#' + kdpId).position().top);
        window.smh('#purchaseWindow').css('left', window.smh('#' + kdpId).position().left);
    },
    startResize: function (kdpId) {
        var isMobile = kWidget.isMobileDevice();
        if (!isMobile) {
            window.smh(window).resize(function () {
                ppv.resizer(kdpId);
            });
        }
    },
    endResize: function () {
        window.smh(window).off("resize");
    },
    fadeLogout: function (kdpId, entryId) {
        window.smh('#purchaseWindow').css('top', window.smh('#' + kdpId).position().top);
        window.smh('#purchaseWindow').css('left', window.smh('#' + kdpId).position().left);
        window.smh('#purchaseWindow').css('width', parseInt(window.smh('#' + kdpId).css('width')));
        window.smh('#purchaseWindow').css('height', '40px');
        window.smh('#purchaseWindow').show();
        var clock = '';
        if (!scheduled_is_before && !scheduled_is_after && scheduled_has_end_date && paid) {
            clock = '<div id="clock"></div>';
        }
        window.smh('#purchaseWindow').html('<div class="topBarContainer hover open"><button class="btn pull-left display-medium tooltipBelow" title="Profile" onclick="ppv_obj.smhProfile();"><i class="fa fa-user"></i><span class="accessibilityLabel">Profile</span></button><button class="btn pull-right display-medium tooltipBelow" title="Logout" onclick="ppv_obj.smhLogout(\'' + entryId + '\');"><span class="accessibilityLabel">Logout</span><i class="fa fa-sign-out"></i></button></div>' + clock);
        ppv.endResize();
        ppv.startResize(kdpId);
        fadeout_timer_m = null;
        window.smh(window).on('touchmove', function () {
            clearTimeout(fadeout_timer_m);
            window.smh('#purchaseWindow').fadeIn();
            fadeout_timer_m = setTimeout('window.smh("#purchaseWindow").fadeOut("slow", function(){ ppv.showButton(); });', 3000);
        });
        fadeout_timer = null;
        if (!paused) {
            fadeout_timer = setTimeout('window.smh("#purchaseWindow").fadeOut("slow", function(){ ppv.showButton(); });', 3000);
        }
        window.smh(document).on('mousemove', '#' + kdpId + ',#purchaseWindow', function () {
            clearTimeout(fadeout_timer);
            window.smh('#purchaseWindow').fadeIn();
            if (!paused) {
                fadeout_timer = setTimeout('window.smh("#purchaseWindow").fadeOut("slow", function(){ ppv.showButton(); });', 3000);
            }
        });
        if (!scheduled_is_before && !scheduled_is_after && scheduled_has_end_date && paid) {
            var scheduled_end_date = moment.tz(end_date, user_timezone_name);
            window.smh('#clock').countdown(scheduled_end_date.toDate(), function (event) {}).on('finish.countdown', function (event) {
                location.reload();
            });
        }
    },
    showButton: function () {
        if (blocked) {
            if (!is_logged_in) {
                window.smh('#purchaseWindow').show();
            }
        }
    },
    loadPayWallFP: function (kdpId, pid, entryId, thumbId, uiconf_width, uiconf_height) {
        var logged_in_content = '';
        var purchased_button = '';
        var buy_now_style = '';
        var top_bar = '';
        var button_id = '';
        var free_preview = '';
        var date_time = '';
        if (!blocked) {
            free_preview = '<div id="fp-wrapper">' +
                    '<a onclick="ppv.watchPreview(\'' + kdpId + '\',\'' + thumbId + '\');"><i class="fa fa-play-circle"></i><span>watch preview</span></a>' +
                    '</div>';
        }
        if (!paid) {
            window.smh('#purchaseWindow').css('top', window.smh('#' + kdpId).position().top);
            window.smh('#purchaseWindow').css('left', window.smh('#' + kdpId).position().left);
            window.smh('#purchaseWindow').css('width', parseInt(window.smh('#' + kdpId).css('width')));
            if (smh_vr && showCompMessage) {
                window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')) - 70);
            } else {
                window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')));
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
            var clock = '';
            if (!scheduled_is_before && !scheduled_is_after && scheduled_has_end_date) {
                clock = '<div id="clock"></div>';
            }
            if (scheduled_has_start_date) {
                var dt_start_date = moment(start_date).tz(timezone).format("MMM DD, YYYY [@] hh:mm A [<sup>]z[</sup>]");
                date_time = '<div id="date-time">' + dt_start_date + '</div>';
            }
            if (!is_logged_in) {
                purchased_button = '<button onclick="loginHandler()" type="button" class="purchasedButton" id="purchasedButton">Already Purchased?</button>';
                top_bar = '';
                logged_in_content = '<div id="register-text">' +
                        'You must have an account to purchase a ticket.<br> Don\'t have an account? <a onclick="ppv_obj.register_window();">Register Here</a>' +
                        '</div>' +
                        '<div class="clear"></div>';
            } else {
                purchased_button = '';
                buy_now_style = 'style="transform: translateY(70%);"';
                top_bar = '<div class="topBarContainer hover open"><button class="btn pull-left display-medium tooltipBelow" title="Profile" onclick="ppv_obj.smhProfile();"><i class="fa fa-user"></i><span class="accessibilityLabel">Profile</span></button><button class="btn pull-right display-medium tooltipBelow" title="Logout" onclick="ppv_obj.smhLogout(\'' + entryId + '\');"><span class="accessibilityLabel">Logout</span><i class="fa fa-sign-out"></i></button></div>';
                logged_in_content = '';
            }

            window.smh('#purchaseWindow').show();
            window.smh('#purchaseWindow').html(top_bar +
                    '<div id="' + button_id + '">' +
                    '<div id="header">' +
                    '<i class="fa fa-lock"></i> <div id="title">' + entry_title + '</div>' +
                    clock +
                    '</div>' +
                    '<div id="content-wrapper">' +
                    '<div class="wrap">' +
                    '<div id="entry-thumb" class="column">' +
                    date_time +
                    '<img width="130px" src="' + protocol + '://images.mediaplatform.streamingmediahosting.com/p/' + pid + '/thumbnail/entry_id/' + thumbId + '/width/130">' +
                    free_preview +
                    '</div>' +
                    '<div id="purchase-ticket" class="column">' +
                    '<button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton" ' + buy_now_style + '>Buy Now</button>' +
                    purchased_button +
                    '</div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    logged_in_content +
                    '</div>' +
                    '</div>');
            ppv.startResize(kdpId);
            if (!scheduled_is_before && !scheduled_is_after && scheduled_has_end_date) {
                var scheduled_end_date = moment.tz(end_date, user_timezone_name);
                window.smh('#clock').countdown(scheduled_end_date.toDate(), function (event) {}).on('finish.countdown', function (event) {
                    location.reload();
                });
            }
        } else {
            if (is_logged_in) {
                ppv.fadeLogout(kdpId, entryId);
            }
        }
    },
    watchPreview: function (kdpId, thumbId) {
        var entry_id = ppv_obj.getConfig('entryId');
        if (smh_vr) {
            kdp.sendNotification("doSeek", 0);
            window.kdp.sendNotification('doPlay');
            paused = false;
            window.smh('#purchaseWindow').hide();
            if (is_logged_in) {
                ppv.fadeLogout(kdpId, entry_id);
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
            setTimeout(function () {
                window.kdp.sendNotification('doPlay');
            }, 1500);
            paused = false;
            window.smh('#purchaseWindow').hide();
            if (is_logged_in) {
                ppv.fadeLogout(kdpId, entry_id);
            }
        }
    },
    loadPayWall: function (kdpId, pid, entryId, thumbId, uiconf_width, uiconf_height) {
        var logged_in_content = '';
        var purchased_button = '';
        var buy_now_style = '';
        var top_bar = '';
        var button_id = '';
        var free_preview = '';
        var count_down = '';
        var window_title = '';
        var purchase_ticket = '';
        var date_time = '';
        if (!blocked && !scheduled_is_before && !scheduled_is_after) {
            free_preview = '<div id="fp-wrapper">' +
                    '<a onclick="ppv.watchPreview(\'' + kdpId + '\',\'' + thumbId + '\');"><i class="fa fa-play-circle"></i><span>watch preview</span></a>' +
                    '</div>';
        }
        if (!paid) {
            window.smh('#purchaseWindow').css('top', window.smh('#' + kdpId).position().top);
            window.smh('#purchaseWindow').css('left', window.smh('#' + kdpId).position().left);
            window.smh('#purchaseWindow').css('width', parseInt(window.smh('#' + kdpId).css('width')));
            if (smh_vr && showCompMessage) {
                window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')) - 70);
            } else {
                window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')));
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
            if (!is_logged_in && !scheduled_is_after) {
                purchased_button = '<button onclick="loginHandler()" type="button" class="purchasedButton" id="purchasedButton">Already Purchased?</button>';
                logged_in_content = '<div id="register-text">' +
                        'You must have an account to purchase a ticket.<br> Don\'t have an account? <a onclick="ppv_obj.register_window();">Register Here</a>' +
                        '</div>' +
                        '<div class="clear"></div>';
            } else if (is_logged_in) {
                buy_now_style = 'style="transform: translateY(70%);"';
                top_bar = '<div class="topBarContainer hover open"><button class="btn pull-left display-medium tooltipBelow" title="Profile" onclick="ppv_obj.smhProfile();"><i class="fa fa-user"></i><span class="accessibilityLabel">Profile</span></button><button class="btn pull-right display-medium tooltipBelow" title="Logout" onclick="ppv_obj.smhLogout(\'' + entryId + '\');"><span class="accessibilityLabel">Logout</span><i class="fa fa-sign-out"></i></button></div>';
            }

            if (livestream) {
                window_title = 'live stream';
            } else if (playlist) {
                window_title = 'playlist';
            } else {
                window_title = 'video';
            }

            if (!scheduled_has_start_date && !scheduled_has_end_date) {
                purchase_ticket = '<div id="purchase-ticket" class="column">' +
                        '<button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton" ' + buy_now_style + '>Buy Now</button>' +
                        purchased_button +
                        '</div>';
            } else if (scheduled_has_start_date && scheduled_is_before && !scheduled_has_end_date) {
                count_down = '<div id="smh-countdown-wrapper">' +
                        '<div id="clock"></div>' +
                        '</div>' +
                        '<div class="clear"></div>';
                purchase_ticket = '<div id="purchase-ticket" class="column">' +
                        '<button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton" ' + buy_now_style + '>Buy Now</button>' +
                        purchased_button +
                        '</div>';

                var dt_start_date = moment(start_date).tz(timezone).format("MMM DD, YYYY [@] hh:mm A [<sup>]z[</sup>]");
                date_time = '<div id="date-time">' + dt_start_date + '</div>';
            } else if (scheduled_has_start_date && !scheduled_is_before && !scheduled_has_end_date) {
                purchase_ticket = '<div id="purchase-ticket" class="column">' +
                        '<button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton" ' + buy_now_style + '>Buy Now</button>' +
                        purchased_button +
                        '</div>';

                var dt_start_date = moment(start_date).tz(timezone).format("MMM DD, YYYY [@] hh:mm A [<sup>]z[</sup>]");
                date_time = '<div id="date-time">' + dt_start_date + '</div>';
            } else if (scheduled_has_start_date && scheduled_has_end_date && scheduled_is_before && !scheduled_is_after) {
                count_down = '<div id="smh-countdown-wrapper">' +
                        '<div id="clock"></div>' +
                        '</div>' +
                        '<div class="clear"></div>';
                purchase_ticket = '<div id="purchase-ticket" class="column">' +
                        '<button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton" ' + buy_now_style + '>Buy Now</button>' +
                        purchased_button +
                        '</div>';

                var dt_start_date = moment(start_date).tz(timezone).format("MMM DD, YYYY [@] hh:mm A [<sup>]z[</sup>]");
                date_time = '<div id="date-time">' + dt_start_date + '</div>';
            } else if (scheduled_has_start_date && scheduled_has_end_date && !scheduled_is_before && !scheduled_is_after) {
                count_down = '<div id="clock"></div>';
                purchase_ticket = '<div id="purchase-ticket" class="column">' +
                        '<button onclick="purchaseHandler()" type="button" class="buyButton" id="buyNowButton" ' + buy_now_style + '>Buy Now</button>' +
                        purchased_button +
                        '</div>';

                var dt_start_date = moment(start_date).tz(timezone).format("MMM DD, YYYY [@] hh:mm A [<sup>]z[</sup>]");
                date_time = '<div id="date-time">' + dt_start_date + '</div>';
            } else if (scheduled_is_after && scheduled_has_end_date) {
                count_down = '<div id="not-available">This ' + window_title + ' is no longer available</div>';
            }

            window.smh('#purchaseWindow').show();
            window.smh('#purchaseWindow').html(top_bar +
                    '<div id="' + button_id + '">' +
                    '<div id="header">' +
                    '<i class="fa fa-lock"></i> <div id="title">' + entry_title +
                    count_down +
                    '</div>' +
                    '</div>' +
                    '<div id="content-wrapper">' +
                    '<div class="wrap">' +
                    '<div id="entry-thumb" class="column">' +
                    date_time +
                    '<img width="130px" src="' + protocol + '://images.mediaplatform.streamingmediahosting.com/p/' + pid + '/thumbnail/entry_id/' + thumbId + '/width/130">' +
                    free_preview +
                    '</div>' +
                    purchase_ticket +
                    '</div>' +
                    '<div class="clear"></div>' +
                    logged_in_content +
                    '</div>' +
                    '</div>');
            ppv.startResize(kdpId);

            if (scheduled_is_before) {
                var scheduled_start_date = moment(start_date).tz(user_timezone_name);
                window.smh('#clock').countdown(scheduled_start_date.toDate(), function (event) {
                    if (countdown) {
                        window.smh(this).html(event.strftime(
                                '<div id="smh-countdown-text">Available to watch in</div>' +
                                '<div class="smh-countdown-time-wrapper">' +
                                '<div class="time">%D</div>' +
                                '<div class="smh-countdown-time">Days</div>' +
                                '</div>' +
                                '<div class="smh-countdown-time-wrapper">' +
                                '<div class="time">%H</div>' +
                                '<div class="smh-countdown-time">Hours</div>' +
                                '</div>' +
                                '<div class="smh-countdown-time-wrapper">' +
                                '<div class="time">%M</div>' +
                                '<div class="smh-countdown-time">Minutes</div>' +
                                '</div>' +
                                '<div style="float: left;">' +
                                '<div class="time">%S</div>' +
                                '<div class="smh-countdown-time">Seconds</div>' +
                                '</div>'
                                ));
                    }
                }).on('finish.countdown', function (event) {
                    location.reload();
                });
            }
            if (!scheduled_is_before && !scheduled_is_after && scheduled_has_end_date) {
                var scheduled_end_date = moment.tz(end_date, user_timezone_name);
                window.smh('#clock').countdown(scheduled_end_date.toDate(), function (event) {}).on('finish.countdown', function (event) {
                    location.reload();
                });
            }
        } else if (scheduled_is_before && paid) {
            if (is_logged_in) {
                ppv.fadeLogout(kdpId, entryId);

                var window_title = '';
                if (livestream) {
                    window_title = 'live stream';
                } else if (playlist) {
                    window_title = 'playlist';
                } else {
                    window_title = 'video';
                }

                var dt_start_date = moment(start_date).tz(timezone).format("MMM DD, YYYY [@] hh:mm A [<sup>]z[</sup>]");
                if (!countdown) {
                    date_time = '';
                    count_down = '<div id="smh-countdown-wrapper">' +
                            '<div id="smh-countdown-paid-text">You will be able to watch this ' + window_title + ' on</div>' +
                            '<div id="entry-title">' + dt_start_date + '</div>' +
                            '<div id="clock"></div>' +
                            '</div>' +
                            '<div class="clear"></div>';
                } else {
                    date_time = '<div id="date-time">' + dt_start_date + '</div>';
                    count_down = '<div id="smh-countdown-wrapper">' +
                            '<div id="smh-countdown-paid-text">You will be able to watch this ' + window_title + ' in</div>' +
                            '<div id="clock"></div>' +
                            '</div>' +
                            '<div class="clear"></div>';
                }


                window.smh('#purchaseWindow').css('top', window.smh('#' + kdpId).position().top);
                window.smh('#purchaseWindow').css('left', window.smh('#' + kdpId).position().left);
                window.smh('#purchaseWindow').css('width', parseInt(window.smh('#' + kdpId).css('width')));
                if (smh_vr && showCompMessage) {
                    window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')) - 70);
                } else {
                    window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')));
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

                top_bar = '<div class="topBarContainer hover open"><button class="btn pull-left display-medium tooltipBelow" title="Profile" onclick="ppv_obj.smhProfile();"><i class="fa fa-user"></i><span class="accessibilityLabel">Profile</span></button><button class="btn pull-right display-medium tooltipBelow" title="Logout" onclick="ppv_obj.smhLogout(\'' + entryId + '\');"><span class="accessibilityLabel">Logout</span><i class="fa fa-sign-out"></i></button></div>';
                window.smh('#purchaseWindow').show();
                window.smh('#purchaseWindow').html(top_bar +
                        '<div id="' + button_id + '">' +
                        '<div id="header">' +
                        '<i class="fa fa-lock"></i>' +
                        count_down +
                        '</div>' +
                        '<div id="content-wrapper">' +
                        '<div id="entry-title">' +
                        entry_title +
                        '</div>' +
                        '<div class="wrap">' +
                        '<div id="entry-thumb" class="column">' +
                        date_time +
                        '<img width="130px" src="' + protocol + '://images.mediaplatform.streamingmediahosting.com/p/' + pid + '/thumbnail/entry_id/' + thumbId + '/width/130">' +
                        '</div>' +
                        '</div>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '</div>');
                ppv.startResize(kdpId);

                var scheduled_start_date = moment.tz(start_date, user_timezone_name);
                window.smh('#clock').countdown(scheduled_start_date.toDate(), function (event) {
                    if (countdown) {
                        window.smh(this).html(event.strftime(
                                '<div class="smh-countdown-time-wrapper">' +
                                '<div class="time">%D</div>' +
                                '<div class="smh-countdown-time">Days</div>' +
                                '</div>' +
                                '<div class="smh-countdown-time-wrapper">' +
                                '<div class="time">%H</div>' +
                                '<div class="smh-countdown-time">Hours</div>' +
                                '</div>' +
                                '<div class="smh-countdown-time-wrapper">' +
                                '<div class="time">%M</div>' +
                                '<div class="smh-countdown-time">Minutes</div>' +
                                '</div>' +
                                '<div style="float: left;">' +
                                '<div class="time">%S</div>' +
                                '<div class="smh-countdown-time">Seconds</div>' +
                                '</div>'
                                ));
                    }
                }).on('finish.countdown', function (event) {
                    location.reload();
                });
            }
        } else if (scheduled_is_after && paid) {
            if (is_logged_in) {
                ppv.fadeLogout(kdpId, entryId);

                var window_title = '';
                if (livestream) {
                    window_title = 'live stream';
                } else if (playlist) {
                    window_title = 'playlist';
                } else {
                    window_title = 'video';
                }

                window.smh('#purchaseWindow').css('top', window.smh('#' + kdpId).position().top);
                window.smh('#purchaseWindow').css('left', window.smh('#' + kdpId).position().left);
                window.smh('#purchaseWindow').css('width', parseInt(window.smh('#' + kdpId).css('width')));
                if (smh_vr && showCompMessage) {
                    window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')) - 70);
                } else {
                    window.smh('#purchaseWindow').css('height', parseInt(window.smh('#' + kdpId).css('height')));
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

                top_bar = '<div class="topBarContainer hover open"><button class="btn pull-left display-medium tooltipBelow" title="Profile" onclick="ppv_obj.smhProfile();"><i class="fa fa-user"></i><span class="accessibilityLabel">Profile</span></button><button class="btn pull-right display-medium tooltipBelow" title="Logout" onclick="ppv_obj.smhLogout(\'' + entryId + '\');"><span class="accessibilityLabel">Logout</span><i class="fa fa-sign-out"></i></button></div>';
                window.smh('#purchaseWindow').show();
                window.smh('#purchaseWindow').html(top_bar +
                        '<div id="' + button_id + '">' +
                        '<div id="header">' +
                        '<i class="fa fa-lock"></i>' +
                        entry_title +
                        '<div id="not-available">This ' + window_title + ' is no longer available</div>' +
                        '</div>' +
                        '<div id="content-wrapper">' +
                        '<div class="wrap">' +
                        '<div id="entry-thumb" class="column">' +
                        '<img width="130px" src="' + protocol + '://images.mediaplatform.streamingmediahosting.com/p/' + pid + '/thumbnail/entry_id/' + thumbId + '/width/130">' +
                        '</div>' +
                        '</div>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '</div>');
                ppv.startResize(kdpId);
            }
        } else {
            if (is_logged_in) {
                ppv.fadeLogout(kdpId, entryId);
            }
        }
    },
    loadVideo: function (ks, pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type) {
        window.smh('#purchaseWindow').empty();
        window.smh('#purchaseWindow').css('display', 'none');
        ppv.endResize();
        if (window.kdp) {
            kWidget.destroy(window.kdp);
            delete(window.kdp);
        }
        var uniqid = +new Date();
        var kdpId = 'smhtarget' + uniqid;
        window.smh('#myVideoContainer').html(
                '<div id="' + kdpId + '" style="width:400px;height:330px"></div>'
                );
        mw.setConfig('Kaltura.LeadWithHTML5', true);
        flashvars = {};
        flashvars.externalInterfaceDisabled = false;
        flashvars.autoPlay = false;
        flashvars.disableAlerts = true;
        flashvars.httpProtocol = protocol;
        if (ks != "" && !scheduled_is_before && !scheduled_is_after)
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
            flashvars.ppv = {
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
                    ppv.loadPayWall(kdpId, pid, entryId, entryId, uiconf_width, uiconf_height);
                },
                'params': {
                    'wmode': 'opaque'
                }
            });
        } else if (type == 'p') {
            flashvars.ppv = {
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
            window.smh.ajax({
                type: "GET",
                url: protocol + "://api.mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=w_get_thumb",
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
                        ppv.loadPayWall(kdpId, pid, entryId, data, uiconf_width, uiconf_height);
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

            flashvars.ppv = {
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
            window.smh.ajax({
                type: "GET",
                url: protocol + "://api.mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=w_get_cat_thumb",
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
                        ppv.loadPayWall(kdpId, pid, entryId, data, uiconf_width, uiconf_height);
                    },
                    'params': {
                        'wmode': 'opaque'
                    }
                });
            });
        }
    }
}

ppv = new ppv_init();
load_smh_ppv();
window.addEventListener("message", function (event) {
    try {
        var action = JSON.parse(event.data);
        if (action['action'] == 'cancel') {
            pptransact.releaseDG();
            window.smh('#smh_purchase_window').modal('hide');
        }
        if (action['paymentStatus'] != '' || action['paymentStatus'] != null) {
            pptransact.releaseDG(action);
        }
    } catch (e) {
        console.log('SMH DEBUG: Invalid JSON');
    }
});