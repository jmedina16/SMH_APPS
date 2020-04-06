(function (mw, $) {
    "use strict";
    mw.kalturaPluginWrapper(function () {
        mw.PluginManager.add('skipForward', mw.KBaseComponent.extend({
            // public properties
            defaultConfig: {
                // the container for the button
                parent: "controlsContainer",
                // the display order ( based on layout )
                order: 71,
                // the display importance, determines when the item is removed from DOM
                displayImportance: 'high',
                // the alignment of the button
                align: "right",
                // custom property and custom value
                seekTime: "30",
                showTooltip: true,
            },
            canSeek: false,
            setup: function () {
                // initialization code goes here.
                this.addBindings();
                if (this.getPlayer().evaluate('{mediaProxy.entry.type}') !== 1) {
                    console.log('TEEEST');
                    this.hide();
                } else {
                    this.addBindings();
                }
            },
            addBindings: function () {
                var _this = this;
                this.bind('updateBufferPercent', function () {
                    _this.canSeek = true;
                });

                this.bind('playerReady', function () {
                    console.log("player is ready");
                    _this.hide();
                });
            },
            getComponent: function () {
                var _this = this;
                if (!this.$el) {
                    this.$el = $('<button />')
                            .attr('title', 'Skip Forward')
                            .addClass('btn icon-next' + this.getCssClass())
                            .click(function () {
                                _this.seek();
                            });
                }

                return this.$el;
            },
            seek: function () {
                if (!this.canSeek) {
                    return false;
                }
                var seekTime = parseFloat(this.getConfig('seekTime'));
                var currentTime = parseFloat(this.getPlayer().currentTime);
                var newCurrentTime = 0;
                newCurrentTime = currentTime + seekTime;
                if (newCurrentTime > parseFloat(this.getPlayer().getDuration())) {
                    newCurrentTime = parseFloat(this.getPlayer().getDuration());
                }
                this.getPlayer().seek(newCurrentTime);
            },
        }));
    });
})(window.mw, window.jQuery);