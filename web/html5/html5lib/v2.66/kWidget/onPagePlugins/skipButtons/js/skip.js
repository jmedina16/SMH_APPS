var kdp = null;
kWidget.readyCallback(function (playerId) {
        var kdp = $('#' + playerId );
        console.log(kdp);
        kdp.kBind("playerReady", function () {
            console.log('SMH TEEST');
            if (kdp.evaluate('{mediaProxy.entry.type}') === 1) {

                console.log('SMH TEEST');

            }
        });

});