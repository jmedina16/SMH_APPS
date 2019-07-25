window.kWidget.addReadyCallback(function (playerId) {
    window.kdp = document.getElementById(playerId);
    console.log(kdp);
    var entry_id;
    var timeout;
    window.kdp.kBind('mediaReady', function () {
        if (kdp.evaluate('{mediaProxy.entry.type}') === 1) {
            entry_id = kdp.evaluate('{mediaProxy.entry.id}');
            kdp.kBind('playbackComplete', function () {
                setCookie("resumevideodata_" + entry_id, 0, -1);
                clearTimeout(timeout);
            });

            var regex = new RegExp('^(.*;)?\s*resumevideodata_' + entry_id + '\s*=\s*[^;]+(.*)?$', "g");
            if (document.cookie.match(regex)) {
                var cookie = document.cookie.match(regex);
                var cookie_split = cookie[0].split('=');
                var cookie_value = cookie_split[1].split(';')[0];
                kdp.sendNotification("doSeek", cookie_value);
                kdp.kBind('firstPlay', function () {
                    timeout = setTimeout(function () {
                        rememberPosition(kdp, entry_id);
                    }, 5000);
                });
            } else {
                kdp.kBind('firstPlay', function () {
                    timeout = setTimeout(function () {
                        rememberPosition(kdp, entry_id);
                    }, 5000);
                });
            }
        }
    });

    function rememberPosition(kdp, entry_id) {
        setCookie("resumevideodata_" + entry_id, Math.round(kdp.evaluate('{video.player.currentTime}')), 7);
        timeout = setTimeout(function () {
            rememberPosition(kdp, entry_id);
        }, 5000);
    }

    function setCookie(c_name, value, expiredays) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + expiredays);
        document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toUTCString());
    }
});