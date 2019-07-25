var entry_id;
var timeout;
mw.kalturaPluginWrapper(function () {
    mw.PluginManager.add('resumePlayback', mw.KBaseComponent.extend({
        setup: function () {
            var kdp = this.getPlayer();
            var _this = this;
            _this.bind('playerReady', function () {
                if (this.evaluate('{mediaProxy.entry.type}') === 1) {
                    entry_id = this.evaluate('{mediaProxy.entry.id}');
                    _this.bind('playbackComplete', function () {
                        _this.setCookie("resumevideodata_" + entry_id, 0, -1);
                        clearTimeout(timeout);
                    });

                    var regex = new RegExp('^(.*;)?\s*resumevideodata_' + entry_id + '\s*=\s*[^;]+(.*)?$', "g");
                    console.log(regex);
                    console.log(document.cookie);
                    if (document.cookie.match(regex)) {
                        console.log('MATCH FOUND');
                        var cookie = document.cookie.match(regex);
                        var cookie_split = cookie[0].split('=');
                        var cookie_value = cookie_split[1].split(';')[0];
                        kdp.sendNotification("doSeek", cookie_value);
                        _this.bind('firstPlay', function () {
                            timeout = setTimeout(function () {
                                _this.rememberPosition(kdp, entry_id);
                            }, 5000);
                        });
                    } else {
                        _this.bind('firstPlay', function () {
                            timeout = setTimeout(function () {
                                _this.rememberPosition(kdp, entry_id);
                            }, 5000);
                        });
                    }
                }
            });
        },
        rememberPosition: function (kdp, entryId) {
            var _this = this;
            this.setCookie("resumevideodata_" + entryId, Math.round(kdp.evaluate('{video.player.currentTime}')), 7);
            timeout = setTimeout(function () {
                _this.rememberPosition(kdp, entry_id);
            }, 5000);
        },
        setCookie: function (c_name, value, expiredays) {
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + expiredays);
            document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toUTCString());
        }
    }));
});