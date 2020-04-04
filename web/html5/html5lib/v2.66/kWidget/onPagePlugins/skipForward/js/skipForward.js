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
                seekTimeConfig: "30",
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
                this.bind('updateBufferPercent', function () {
                    _this.canSeek = true;
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
                var seekTime = parseFloat(this.getConfig('seekTimeConfig'));
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