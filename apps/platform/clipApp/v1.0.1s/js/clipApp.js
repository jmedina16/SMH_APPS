window.clipApp = {};

// Log function
clipApp.log = function (msg) {
    if (this.vars.debug) {
        if (typeof console != 'undefined' && console.log) {
            console.log('ClipApp :: ' + msg);
        }
    }
};

// Set default vars
clipApp.vars = {
    host: "www.kaltura.com",
    redirect_save: false,
    redirect_url: "http://www.kaltura.com/",
    overwrite_entry: false,
    seekFromKClip: false,
    debug: true
};

clipApp.init = function (options) {
    $.extend(this.vars, options);
};

var jsCallbackReady = function (videoId) {
    clipApp.kdp = $("#" + videoId).get(0);
    clipApp.kdp.addJsListener("mediaReady", "clipApp.player.doFirstPlay");
    clipApp.kdp.addJsListener("playerPlayed", "clipApp.player.playerPlaying");
    clipApp.kdp.addJsListener("playerPaused", "clipApp.player.playerPaused");
    //clipApp.kdp.addJsListener("doSeek", "clipApp.onSeek");
    clipApp.kdp.addJsListener("doSeek", "clipApp.playerSeek");
    clipApp.kdp.addJsListener("durationChange", "clipApp.player.durationChange");
    clipApp.clipper.addClip();

};

var clipperReady = function () {
    clipApp.kClip = $("#clipper").get(0);
    clipApp.kClip.addJsListener("clipStartChanged", "clipApp.updateStartTime");
    clipApp.kClip.addJsListener("clipEndChanged", "clipApp.updateEndTime");
    clipApp.kClip.addJsListener("entryReady", "clipApp.enableAddClip");
    clipApp.kClip.addJsListener("clipAdded", "clipApp.clipAdded");
    clipApp.kClip.addJsListener("clipperError", "clipApp.showError");
    clipApp.kClip.addJsListener("playheadDragStart", "clipApp.clipper.dragStarted");
    clipApp.kClip.addJsListener("playheadDragDrop", "clipApp.player.updatePlayhead")
};

/* Init the App */
$(function () {
    clipApp.log('Init App');

    clipApp.createTimeSteppers();
    clipApp.activateButtons();

});

// Contains all player related functions
clipApp.player = {
    doFirstPlay: function () {
        clipApp.log('doFirstPlay');
        clipApp.player.firstLoad = true;
        clipApp.kdp.sendNotification("doPlay");
    },

    playerPlaying: function () {
        clipApp.log('clipApp.player.playerPlaying');
        if (clipApp.player.firstLoad) {
            clipApp.log('pauseKdp');
            // SMH Player Ready Work-around
            clipApp.pageReady();
            clipApp.player.firstLoad = false;
            setTimeout(function () {
                clipApp.kdp.sendNotification("doPause");
            }, 50);
        }
        clipApp.vars.removeBlackScreen = true;
        clipApp.vars.playerPlaying = true;

        clipApp.kClip.removeJsListener("playheadUpdated", "clipApp.player.updatePlayhead");
        //clipApp.kdp.addJsListener("playerUpdatePlayhead", "clipApp.clipper.updatePlayhead");
        clipApp.kdp.addJSListener("playerUpdatePlayhead", "SMHupdatePlayhead");
    },

    playerPaused: function () {
        clipApp.vars.playerPlaying = false;
        //clipApp.kClip.addJsListener("playheadUpdated", "clipApp.player.updatePlayhead");
        clipApp.kdp.removeJsListener("playerUpdatePlayhead", "clipApp.clipper.updatePlayhead");
    },

    updatePlayhead: function (val) {
//        clipApp.clipper.dragging = false;
//        if (clipApp.clipper.dragging === false) {
//            clipApp.kClip.addJsListener("playheadUpdated", "clipApp.player.updatePlayhead");
//        }

        val = Math.floor(val / 1000);
        clipApp.vars.seekFromKClip = true;
        clipApp.kdp.sendNotification("doSeek", val);
        setTimeout(function () {
            clipApp.kdp.sendNotification("doPause");
        }, 250);
    },

    durationChange: function (data) {
        clipApp.vars.entry.msDuration = data.newValue * 1000;
        clipApp.kClip.setDuration(data.newValue * 1000);

    }
};

// Contains all clipper related functions
clipApp.clipper = {
    dragging: false,
    addClip: function (start, end) {
        //var clip_length = (end) ? end : (clipApp.getMsDuration() / 10); // Get 10 percent of video duration
        var clip_length = (end) ? end : (((clipApp.getDuration() * 10) / 100) * 1000);
        //var clip_offset = (start) ? start : clipApp.kClip.getPlayheadLocation();
        //	SMH Get this val from new slider
        var clip_offset = (start) ? start : $('#jqui').slider('value');
        clip_length = Math.round(clip_length);
        clip_offset = Math.round(clip_offset);
        //clipApp.kClip.addClipAt(clip_offset, clip_length);
        clipApp.addClipAt(clip_offset, clip_length);
        clipApp.log('addClipAt (Length: ' + clip_length + ')');
        clipApp.kdp.sendNotification("doPause");
    },

    updatePlayhead: function (val) {
        //clipApp.kClip.scrollToPoint(val * 1000);
    },
    dragStarted: function () {
        //clipApp.clipper.dragging = true;
        //clipApp.kClip.removeJsListener("playheadUpdated", "clipApp.player.updatePlayhead");
    }
};

// SMH Custom function to replace clipper...
clipApp.addClipAt = function (clip_offset, clip_length) {
    // create same object as returned from clipApp.KClip.addClipAt() function call...

    var clip = {
        "clipAttributes": {
            "duration": clip_length,
            "offset": clip_offset,
            "uid": ''
        },
        "entry": {},
        "id": ''
    };

    clipApp.clipAdded(clip);
}

// SMH updateClip => setClip...
clipApp.updateClip = function (x) {
    // create same object as returned from clipApp.KClip.addClipAt() function call...
    clipApp.log('SMH DEBUG: ');
    console.log(x);
    var clip =
            {
                "clipAttributes":
                        {
                            "duration": x.duration,
                            "offset": x.offset,
                            "uid": ''
                        },
                "entry": {},
                "id": ''
            }

    clipApp.log('clipApp.updateAttributes: duration: ' + x.duration + ', offset: ' + x.offset);

    clipApp.updateStartTime(clip);
    clipApp.updateEndTime(clip);
}

clipApp.m = function (msgKey) {
    return this.vars.messages[ msgKey ] || '';
};

clipApp.clipAdded = function (clip) {
    clipApp.updateStartTime(clip);
    clipApp.updateEndTime(clip);
    clipApp.addClipForm();
    // Enable range slider
    $('#jqclip').show();
    ttipUpdate();
};

clipApp.addClipForm = function () {
//    if ($("#newclip").find('.disable').length == 0) {
//        $("#newclip").prepend(clipApp.disableDiv());
//    }
    $("#fields").show().find('.disable').remove();
    $("#actions").find('.disable').remove();

    if (clipApp.vars.overwrite_entry) {
        $("#delete").remove();
        $(".seperator").remove();
    }

    $("#save").find('.disable').remove();
    $("#embed").hide();
};

clipApp.disableDiv = function () {
    return $('<div />').addClass('disable');
};

clipApp.createTimeSteppers = function () {
    clipApp.log('Create Time Steppers');
    $("#startTime").timeStepper({
        onChange: function (val) {
            clipApp.setStartTime(val);
            // Range slider start time
            $('#jqclip').slider("values", 0, val);
        }
    });
    $("#endTime").timeStepper({
        onChange: function (val) {
            clipApp.setEndTime(val);
            // Range slider end time
            $('#jqclip').slider("values", 1, val);
        }
    });
};

clipApp.checkClipDuration = function (val, type) {

    var minLength = 0;
    if (type == 'start') {
        minLength = $("#endTime").timeStepper('getValue') - val;
    } else if (type == 'end') {
        minLength = val - $("#startTime").timeStepper('getValue');
    }

    if (type == 'start' && (val > $("#endTime").timeStepper('getValue'))) {
        alert(this.m('start_time_error'));
        return false;
    }

    if (minLength <= 100) {
        alert(this.m('clip_duration_error'));
        return false;
    }

    if (val > (clipApp.getMsDuration())) {
        return false;
    }

    return true;
};

clipApp.updateStartTime = function (clip) {
    var startTime = Math.round(clip.clipAttributes.offset);
    if ($("#startTime").timeStepper('getValue') == startTime) {
        return;
    }

    clipApp.log('TimeStepper :: Set startTime: ' + startTime);
    $("#startTime").timeStepper('setValue', startTime);
    clipApp.vars.lastStartTime = startTime;
    // Range slider
    $('#jqclip').slider("values", 0, startTime);
    // Main slider
    $('#jqui').slider("value", startTime);
    // tooltips
    ttipUpdate();
};

clipApp.updateEndTime = function (clip) {
    var endTime = Math.round(clip.clipAttributes.offset + clip.clipAttributes.duration);
    if ($("#endTime").timeStepper('getValue') == endTime || endTime <= 0) {
        return;
    }

    clipApp.log('TimeStepper :: Set endTime: ' + endTime);
    $("#endTime").timeStepper('setValue', endTime);
    clipApp.vars.lastEndTime = endTime;
    // Range slider
    $('#jqclip').slider("values", 1, endTime);
    // tooltips
    ttipUpdate();
};

clipApp.setStartTime = function (val) {
    console.log('SMH DEBUG: setStartTime1: '+val)
    console.log('SMH DEBUG: setStartTime2: '+clipApp.vars.lastStartTime)
    var startTime = Math.round(val);
    if (!clipApp.checkClipDuration(startTime, 'start')) {
        $("#startTime").timeStepper('setValue', clipApp.vars.lastStartTime);
        // Range Slider update
        //$('#jqclip').slider("values", 0, clipApp.vars.lastStartTime);
        $('#jqclip').slider("values", clipApp.vars.lastStartTime);
        // Main slider
        $('#jqui').slider("value", clipApp.vars.lastStartTime);
        // tooltips
        ttipUpdate();
        return;
    }

    var clipAttributes = {
        offset: startTime,
        duration: $("#endTime").timeStepper('getValue') - startTime
    };

    // SMH Mod
    //clipApp.kClip.updateClipAttributes( clipAttributes );
    clipApp.updateClip(clipAttributes);
    clipApp.kdp.sendNotification("doPause");
};

clipApp.setEndTime = function (val) {
    var endTime = Math.round(val);
    if (!clipApp.checkClipDuration(endTime, 'end')) {
        $("#endTime").timeStepper('setValue', clipApp.vars.lastEndTime);
        // Range Slider update
        $('#jqclip').slider("values", 1, clipApp.vars.lastEndTime);
        // tooltips
        ttipUpdate();
        return;
    }

    var clipAttributes = {
        offset: $("#startTime").timeStepper('getValue'),
        duration: endTime - $("#startTime").timeStepper('getValue')
    };

    // SMH Mod
    //clipApp.kClip.updateClipAttributes( clipAttributes );
    clipApp.updateClip(clipAttributes);
    clipApp.kdp.sendNotification("doPause");
};

clipApp.activateButtons = function () {
    clipApp.log('Activate Buttons');

//    $("#newclip a").click(function () {
//        clipApp.clipper.addClip();
//    });

    $("#preview").click(function () {
        clipApp.doPreview();
    });

    $("#setStartTime").click(function () {
        //clipApp.setStartTime(clipApp.kClip.getPlayheadLocation());
        clipApp.setStartTime(clipApp.getPlayLocation());
    });

    $("#setEndTime").click(function () {
        //clipApp.setEndTime(clipApp.kClip.getPlayheadLocation());
        clipApp.setEndTime(clipApp.getPlayLocation());
    });

    $("#delete").click(function () {
        if (confirm(clipApp.m('delete_confirmation'))) {
            clipApp.deleteClip();
        }
        return false;
    });

    $("#save a").click(function () {
        clipApp.doSave();
        return false;
    });
};

clipApp.enableAddClip = function () {
    if (clipApp.vars.overwrite_entry) {
        clipApp.log('Add new clip for trimming', (clipApp.getMsDuration()));
        clipApp.clipper.addClip(0, (clipApp.getMsDuration()));
    }
    //$("#newclip").find('.disable').remove();
};

clipApp.doPreview = function () {
    var startTime = $("#startTime").timeStepper('getValue', 'seconds'),
            endTime = $("#endTime").timeStepper('getValue', 'seconds');

    clipApp.log('Start Time: ' + startTime + ', End Time: ' + endTime);

    clipApp.vars.removeBlackScreen = false;

    clipApp.kdp.removeJsListener("doSeek", "clipApp.onSeek");
    //clipApp.kClip.updateZoomIndex(0);

    if (clipApp.vars.playerPlaying) {
        clipApp.kdp.sendNotification("doPause");
    }
    clipApp.kdp.setKDPAttribute("blackScreen", "visible", "true");
    clipApp.kdp.setKDPAttribute("mediaProxy", "mediaPlayFrom", startTime);
    clipApp.kdp.setKDPAttribute("mediaProxy", "mediaPlayTo", endTime);
    // work around for kdp didn't play at first doPlay
    clipApp.kdp.sendNotification("doPlay");
    clipApp.kdp.sendNotification("doPlay");

    clipApp.kdp.addJsListener("doSeek", "clipApp.onSeek");
};

clipApp.onSeek = function (val) {
    if (clipApp.vars.removeBlackScreen) {
        clipApp.log('onSeek :: Remove black screen');
        clipApp.kdp.setKDPAttribute("blackScreen", "visible", "false");
    }

    if (clipApp.vars.seekFromKClip === false) {
        clipApp.clipper.updatePlayhead(val);
    } else {
        clipApp.vars.seekFromKClip = false;
    }
};

clipApp.playerSeek = function (val) {
    clipApp.log('playerSeek :: val: ' + val);
    val2 = val * 1000;
    time = clipApp.getTime(val);
    $('#jqui').slider("option", "value", val2);
    $('#jqui a').attr('title', clipApp.getTime(val));
    clipApp.log('playerSeek :: Update Time to: ' + clipApp.getTime(val));
    changed = false;
}

clipApp.getTime = function (val) {
    return (new Date).clearTime().addSeconds(val).toString('hh:mm:ss');
}

clipApp.getPlayLocation = function () {
    return $('#jqui').slider('value');
}

clipApp.getDuration = function () {
    return clipApp.vars.entry.duration;
};
clipApp.getMsDuration = function () {
    return clipApp.vars.entry.msDuration;
};

clipApp.showError = function (error) {
    alert(error.messageText);
};

clipApp.getEmbedCode = function (entry_id) {

    var unique_id = clipApp.getUniqueId();
    var entry_url = 'http://' + clipApp.vars.host + '/kwidget/wid/_' + clipApp.vars.partner_id + '/uiconf_id/' + clipApp.vars.kdp_uiconf_id + '/entry_id/' + entry_id;

    var embed_code = '<object id="kaltura_player_' + unique_id + '" name="kaltura_player_' + unique_id + '" type="application/x-shockwave-flash" allowFullScreen="true"' +
            ' allowNetworking="all" allowScriptAccess="always" height="330" width="400" bgcolor="#000000"' +
            ' resource="' + entry_url + '" data="' + entry_url + '"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" />' +
            ' <param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="movie" value="' + entry_url + '" /></object>';

    return embed_code;
};

clipApp.getUniqueId = function () {
    var d = new Date();
    return d.getTime().toString().substring(4);
};

clipApp.showEmbed = function (entry_id, entry_name) {
    // Hide current elements
    clipApp.deleteClip();

    // Set embed code
    $("#embedcode").click(function () {
        this.select();
    });
    $("#embedcode").val(clipApp.getEmbedCode(entry_id));

    // Search & Replace for [title] & [entryId] in save message
    var saveMessage = $("#embed").find("p").html();
    saveMessage = saveMessage.replace("@title@", entry_name);
    saveMessage = saveMessage.replace("@entryId@", entry_id);

    $("#embed").find("p").html(saveMessage);

    // Show embed code
    $("#fields").hide();
    //$("#newclip").find('.disable').remove();
    $("#embed").show();
};

clipApp.doSave = function () {
    if (($("#endTime").timeStepper('getValue') - $("#startTime").timeStepper('getValue')) <= 100) {
        alert(this.m('clip_duration_error'));
        return;
    }

    $("#loader").fadeIn();

    //$("#newclip").prepend(clipApp.disableDiv());
    $("#fields").prepend(clipApp.disableDiv());
    $("#actions").prepend(clipApp.disableDiv());
    $("#save").prepend(clipApp.disableDiv());

    // Get Params
    var params = {
        'entryId': clipApp.vars.entry.id,
        'mediaType': clipApp.vars.entry.mediaType,
        'name': $("#entry_title").val(),
        'desc': $("#entry_desc").val(),
        'start': $("#startTime").timeStepper('getValue'),
        'end': $("#endTime").timeStepper('getValue'),
        'ks': clipApp.vars.ks
    };

    var saveUrl = 'save.php';
    if (clipApp.vars.config) {
        var queryString = $.param({
            'config': clipApp.vars.config,
            'partnerId': clipApp.vars.partner_id,
            'kclipUiconf': clipApp.vars.kclip_uiconf_id,
            'kdpUiconf': clipApp.vars.kdp_uiconf_id,
            'mode': ((clipApp.vars.overwrite_entry) ? 'trim' : 'clip')
        });
        saveUrl += '?' + queryString;
    }

    // Make the request
    $.ajax({
        url: saveUrl,
        type: "post",
        data: params,
        dataType: "json",
        success: function (res) {
            $("#loader").fadeOut();
            if (res.error) {
                alert(res.error);
            }
            if (clipApp.vars.redirect_save === true) {
                window.location.href = clipApp.vars.redirect_url;
            } else {
                clipApp.showEmbed(res.id, res.name);
            }
        }
    });
};

clipApp.deleteClip = function () {
    // Stop the KDP
    clipApp.kdp.sendNotification("doPause");

    // Remove clip from clipper
    //clipApp.kClip.deleteSelected();

    // Reset fields
    $("#entry_title").val(clipApp.vars.entry.name);
    if (!clipApp.vars.entry.description) {
        clipApp.vars.entry.description = '';
    }
    $("#entry_desc").val(clipApp.vars.entry.description || '');

//    if (!clipApp.vars.overwrite_entry) {
//        $("#newclip").find('.disable').remove();
//    }
    $("#fields").prepend(clipApp.disableDiv());
    $("#actions").prepend(clipApp.disableDiv());
    $("#save").prepend(clipApp.disableDiv());

    // Range Slider Reset
    clipc = clipApp.vars.lastStartTime / 10;
    clipend = clipApp.vars.lastStartTime + clipc;
    $('#jqclip').hide();
    $('#jqclip').slider("values", [clipApp.vars.lastStartTime, clipend]);
};

clipApp.pageReady = function () {
    clipApp.enableAddClip();
    $('#jqui a').attr('title', clipApp.getTime(0));
    $("#jqui").show();
}

// SMH JS Related functionality 

$(document).ready(function () {

    // Setup Vars
    entryDuration = clipApp.getDuration();
    entryDurationMs = entryDuration * 1000;
    changed = false;

    $("#jqui").hide();
    $("#jqui").slider({
        min: 0,
        max: entryDurationMs,
        step: 1,

        stop: function (event, ui) {
            time = ui.value / 1000;
            times = ui.value;
            // update player preview
            //	clipApp.kdp.sendNotification("doSeek", time);
            clipApp.onSeek(time);
        },

//        change: function (event, ui) {
//            if (!changed) {
//                changed = true;
//                time = ui.value / 1000;
//                clipApp.kdp.sendNotification("doSeek", time);
//            }
////            time = ui.value / 1000;
////            clipApp.kdp.sendNotification("doSeek", time);
//        }
    });
    $("#jqclip").hide();
    $("#jqclip").slider({
        min: 0,
        max: entryDurationMs,
        step: 1,
        range: true,

        stop: function (event, ui) {
            vals = $('#jqclip').slider("values");
            if (vals[0] !== clipApp.vars.lastStartTime) {
                clipApp.log('jqclip.updateStartTime: ' + vals[0]);
                clipApp.setStartTime(vals[0]);
                // update player preview
                //		clipApp.kdp.sendNotification("doSeek", vals[0]);
                //		clipApp.onSeek(vals[0]/1000);
            }
            if (vals[1] !== clipApp.vars.lastEndTime) {
                clipApp.log('jqclip.updateEndTime: clipApp.setEndTime(' + vals[1] + ')');
                clipApp.setEndTime(vals[1]);
            }
        }
    });

    // populate times
    $('#jqtime-end').html((new Date).clearTime().addSeconds(entryDuration).toString('mm:ss'));
    nodes = 1;
    while (nodes < 8) {
        total = entryDuration;
        step = entryDuration / 8;
        add = step * nodes;
        $('#jqtime-' + nodes).html(
                (new Date).clearTime().addSeconds(add).toString('mm:ss')
                );
        nodes++;
    }
});

// Range tooltips
ttipUpdate = function () {
    clipt = $('#jqclip').slider('values');
    clipu = clipt[1] - clipt[0];
    clipd = clipu / 1000;
    $('div.ui-slider-range').attr('title', "Duration: " + (new Date).clearTime().addSeconds(clipd).toString('hh:mm:ss'));
    $(document).tooltip();
}

SMHupdatePlayhead = function (x) {
    $("#jqui").slider("option", "value", x);
}