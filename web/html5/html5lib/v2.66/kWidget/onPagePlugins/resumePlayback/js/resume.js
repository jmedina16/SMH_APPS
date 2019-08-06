var entry_id;
var timeout;
mw.kalturaPluginWrapper(function () {
    mw.PluginManager.add('resumePlayback', mw.KBaseComponent.extend({
        setup: function () {
            var kdp = this.getPlayer();
            var _this = this;
            var autoplay = false;
            _this.bind('playerReady', function () {
                autoplay = this.evaluate('{autoPlay}');
                if (this.evaluate('{mediaProxy.entry.type}') === 1) {
                    entry_id = this.evaluate('{mediaProxy.entry.id}');
                    _this.bind('playbackComplete', function () {
                        _this.setCookie("resumevideodata_" + entry_id, 0, -1);
                        clearTimeout(timeout);
                    });

                    var cookie = _this.getCookie('resumevideodata_' + entry_id);
                    if (cookie !== "") {
                        _this.bind('firstPlay', function () {
                            timeout = setTimeout(function () {
                                _this.rememberPosition(kdp, entry_id);
                            }, 5000);
                        });
                        _this.doSeek(cookie, autoplay);
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
        doSeek: function (seconds, autoplay) {
            var kdp = this.getPlayer();
            var _this = this;
            if (mw.isIE() || this.isEdge() || mw.isMobileDevice()) {
                kdp.sendNotification('doPlay');
                kdp.sendNotification('doSeek', seconds);
                this.bind('seeked', function () {
                    setTimeout(function () {
                        kdp.sendNotification('doPause');
                        _this.unbind('seeked');
                    }, 1500);
                });
            } else {
                kdp.sendNotification("doSeek", seconds);
                if (autoplay) {
                    this.bind('seeked', function () {
                        setTimeout(function () {
                            kdp.sendNotification('doPlay');
                            _this.unbind('seeked');
                        }, 1500);
                    });
                }
            }
        },
        isEdge: function () {
            return ((navigator.userAgent.indexOf('Edge') != -1));
        },
        getCookie: function (cname) {
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