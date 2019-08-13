var entry_id;
var timeout;
kWidget.addReadyCallback(function (playerId) {
    var kdp = document.getElementById(playerId);
    kdp.kBind("playerReady", function () {
        if (kdp.evaluate('{mediaProxy.entry.type}') === 1) {
            entry_id = kdp.evaluate('{mediaProxy.entry.id}');
            kdp.kBind('playbackComplete', function () {
                setCookie("resumevideodata_" + entry_id, 0, -1);
                clearTimeout(timeout);
            });

            var cookie = getCookie('resumevideodata_' + entry_id);
            if (cookie !== "") {
                timeout = setTimeout(function () {
                    rememberPosition(kdp, entry_id);
                }, 5000);
                doSeek(kdp, cookie);
            } else {
                timeout = setTimeout(function () {
                    rememberPosition(kdp, entry_id);
                }, 5000);
            }
        }
    });
});

function doSeek(kdp, seconds) {
    if (kWidget.isMobileDevice()) {
        kdp.sendNotification('doPlay');
        kdp.sendNotification('doSeek', seconds);
        kdp.kBind('seeked', function () {
            setTimeout(function () {
                kdp.sendNotification('doPause');
                kdp.kUnbind('seeked');
            }, 1500);
        });
    } else {
        kdp.sendNotification("doSeek", seconds);
    }
}
function isEdge() {
    return ((navigator.userAgent.indexOf('Edge') != -1));
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function rememberPosition(kdp, entryId) {
    setCookie("resumevideodata_" + entryId, Math.round(kdp.evaluate('{video.player.currentTime}')), 7);
    timeout = setTimeout(function () {
        rememberPosition(kdp, entry_id);
    }, 5000);
}
function setCookie(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toUTCString());
}