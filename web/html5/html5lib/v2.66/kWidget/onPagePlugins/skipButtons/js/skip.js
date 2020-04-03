var kdp = null;
console.log('TEEEST');
kWidget.addReadyCallback(function (playerId) {
    console.log('READY CALL BACK FIRED');
    var kdp = document.getElementById(playerId);
    var playerType = kdp.nodeName.toLowerCase();
    if (playerType == "object") {
        kdp.kBind("entryReady", function () {
           console.log('SMH TEEST');
        });
    } else {
        kdp.kBind("playerReady", function () {
            console.log('SMH TEEST');
        });
    }
//    console.log(kdp);
//    kdp.kBind("playerReady", function () {
//        console.log('SMH TEEST');
//        if (kdp.evaluate('{mediaProxy.entry.type}') === 1) {
//
//            console.log('SMH TEEST');
//
//        }
//    });

});