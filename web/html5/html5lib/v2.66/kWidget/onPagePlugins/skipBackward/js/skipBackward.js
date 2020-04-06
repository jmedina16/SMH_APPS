(function (mw, $) {
    "use strict";
    mw.kalturaPluginWrapper(function () {
        mw.PluginManager.add('skipBackward', mw.KBaseComponent.extend({
            // public properties
            defaultConfig: {
                // the container for the button
                parent: "controlsContainer",
                // the display order ( based on layout )
                order: 81,
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
                // call a method for event bindings:
                this.addBindings();
            },
            addBindings: function () {
                var _this = this;
                this.bind('playerReady', function () {
                    if (_this.getPlayer().evaluate('{mediaProxy.entry.type}') === 1) {
                        _this.bind('updateBufferPercent', function () {
                            _this.canSeek = true;
                        });
                    } else {
                        _this.hide();
                    }
                });
            },
            getComponent: function () {
                var _this = this;
                if (!this.$el) {
                    this.$el = $('<button />')
                            .attr('title', 'Skip Backward')
                            .addClass('btn icon-prev' + this.getCssClass())
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
                newCurrentTime = currentTime - seekTime;
                if (newCurrentTime < 0) {
                    newCurrentTime = 0;
                }
                this.getPlayer().seek(newCurrentTime);
            },
        }));
    });
})(window.mw, window.jQuery);