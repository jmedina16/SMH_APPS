kWidget.addReadyCallback(function (playerId) {
    var kdp = document.getElementById(playerId);
    console.log(kdp);
    kdp.kBind("playerReady", function () {
        if (kdp.evaluate('{mediaProxy.entry.type}') === 1) {

            console.log('SMH TEEST');

        }
    });
});