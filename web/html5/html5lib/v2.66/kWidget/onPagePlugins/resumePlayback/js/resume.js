var entry_id;
var timeout;
mw.kalturaPluginWrapper(function () {
    mw.PluginManager.add('resumePlayback', mw.KBaseComponent.extend({
        setup: function () {
            var kdp = this.getPlayer();
            console.log(kdp);
            kdp.kBind('mediaReady', function () {
                console.log('mediaReady');
            });
            // initialization code goes here.
            // call a method for event bindings:
            //this.addBindings();
        },
        addBindings: function () {
            this.kBind('playerReady', function () {
                console.log('mediaReady');
                if (this.evaluate('{mediaProxy.entry.type}') === 1) {
                    entry_id = this.evaluate('{mediaProxy.entry.id}');
                    this.bind('playbackComplete', function () {
                        this.setCookie("resumevideodata_" + entry_id, 0, -1);
                        clearTimeout(timeout);
                    });

                    var regex = new RegExp('^(.*;)?\s*resumevideodata_' + entry_id + '\s*=\s*[^;]+(.*)?$', "g");
                    if (document.cookie.match(regex)) {
                        var cookie = document.cookie.match(regex);
                        var cookie_split = cookie[0].split('=');
                        var cookie_value = cookie_split[1].split(';')[0];
                        this.getPlayer().sendNotification("doSeek", cookie_value);
                        this.kBind('firstPlay', function () {
                            timeout = setTimeout(function () {
                                this.rememberPosition(kdp, entry_id);
                            }, 5000);
                        });
                    } else {
                        this.kBind('firstPlay', function () {
                            timeout = setTimeout(function () {
                                this.rememberPosition(kdp, entry_id);
                            }, 5000);
                        });
                    }
                }
            });
        },
        rememberPosition: function (kdp, entryId) {
            this.setCookie("resumevideodata_" + entryId, Math.round(kdp.evaluate('{video.player.currentTime}')), 7);
            timeout = setTimeout(function () {
                rememberPosition(kdp, entry_id);
            }, 5000);
        },
        setCookie: function (c_name, value, expiredays) {
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + expiredays);
            document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toUTCString());
        }
    }));
});