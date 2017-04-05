// JavaScript Document

var dg = new PAYPAL.apps.DGFlow({});

var pptransact = function () {
    var url;
    var mobile;

    return{
        init: function (protocol, mobileFlag) {
            this.mobile = (mobileFlag == true) ? true : false;
            this.url = protocol + "://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv_dev/resources/php/pptransact.php";
        },
        setUrl: function (newUrl) {
            this.url = newUrl;
        },
        getUrl: function () {
            return this.url;
        },
        bill: function (inputArgs) {
            var userId = encodeURIComponent(inputArgs.userId);
            var ticketId = encodeURIComponent(inputArgs.ticketId);
            var ticket_type = encodeURIComponent(inputArgs.ticket_type);
            var bill_per = encodeURIComponent(inputArgs.bill_per);
            var entryId = encodeURIComponent(inputArgs.entryId);
            var kentry = encodeURIComponent(inputArgs.kentry);
            var orderId = encodeURIComponent(inputArgs.orderId);
            var subId = encodeURIComponent(inputArgs.subId);
            var qty = encodeURIComponent(inputArgs.itemQty);
            var pid = encodeURIComponent(inputArgs.pid);
            var sm_ak = encodeURIComponent(inputArgs.sm_ak);
            var type = encodeURIComponent(inputArgs.type);
            var protocol = encodeURIComponent(inputArgs.protocol);
            var url = encodeURIComponent(inputArgs.url);
            var tz = encodeURIComponent(inputArgs.tz);
            pptransact.setUserId(userId);
            pptransact.setSuccessBillCallBack(inputArgs.successCallback);
            pptransact.setFailBillCallBack(inputArgs.failCallback);

            var data = 'method=getToken&entryId=' + entryId + "&qty=" + qty + "&userId=" + userId + "&mobile=" + this.mobile + "&pid=" + pid + "&ticketId=" + ticketId + "&kentry=" + kentry + "&orderId=" + orderId + "&sm_ak=" + sm_ak + "&type=" + type + "&ticket_type=" + ticket_type + "&bill_per=" + bill_per + "&subId=" + subId + "&protocol=" + protocol + "&url=" + url + "&tz=" + tz;
            pptransact.callServer(data, function (data) {
                if (data.error) {
                    alert('error starting bill flow');
                } else {
                    if (typeof inputArgs.successCallback == 'function') {
                        //what is this for?!
                        //inputArgs.successCallback.call();
                    }
                    pptransact.startDGFlow(data.redirecturl);
                }
            }, inputArgs.failCallback);
        },
        setSuccessBillCallBack: function (newSuccessBillCallBack) {
            this.successBillCallBack = newSuccessBillCallBack;
        },
        getSuccessBillCallBack: function () {
            return this.successBillCallBack;
        },
        setFailBillCallBack: function (newFailBillCallBack) {
            this.failBillCallBack = newFailBillCallBack;
        },
        getFailBillCallBack: function () {
            return this.failBillCallBack;
        },
        setState: function (newState) {
            state = newState;
        },
        getState: function () {
            return state;
        },
        setUserId: function (newUserId) {
            userId = newUserId;
        },
        getUserId: function () {
            return userId;
        },
        setVerifyData: function (newVerifyData) {
            verifyData = newVerifyData;
        },
        getVerifyData: function () {
            return verifyData;
        },
        verify: function (inputArgs) {
            var userId = encodeURIComponent(inputArgs.userId);

            pptransact.setUserId(userId);
            data = localStorage.getItem(userId);

            if (data == null) {
                data = '[{"transactionId":null,"orderTime":null,"paymentStatus":null,"itemId":"0","userId":"0"}]';
                //return {'error' : 'no local storage record found'};
            }

            data = data.replace(/\\/g, "");

            pptransact.callServer('method=verifyPayment&userId=' + userId + '&transactions=' + encodeURIComponent(data) + '&itemId=' + encodeURIComponent(inputArgs.itemId), function (data) {
                pptransact.setVerifyData(data);

                if (data.success) {
                    if (pptransact.check_for_html5_storage) {
                        var dataArray = $smh.parseJSON(localStorage.getItem(pptransact.getUserId()));

                        if (dataArray !== null) {
                            //REMOVE ANY NULL TransactionIDs
                            for (var i = 0; i < dataArray.length; i++) {
                                if (!dataArray[i].transactionId) {
                                    dataArray.splice(i, 1);
                                }
                            }

                            //UPDATE any Existing  TransactionIDs which match.
                            for (var i = 0; i < dataArray.length; i++) {
                                if (data.transactionId == dataArray[i].transactionId) {
                                    dataArray.splice(i, 1, data);

                                    localStorage.setItem(pptransact.getUserId(), JSON.stringify(dataArray));
                                }

                            }

                        }
                    }

                    if (typeof inputArgs.successCallback == 'function') {
                        inputArgs.successCallback.call();
                    }

                } else {
                    if (typeof inputArgs.failCallback == 'function') {
                        inputArgs.failCallback.call();
                    }
                }
            }, inputArgs.failCallback);
        },
        startDGFlow: function (token) {
            console.log('TEEEESSTT1: ' + token);
            //(this.mobile) ? window.location = url + "&cmd=_express-checkout" : dg.startFlow(url);
            //(this.mobile) ? window.location = url + "&cmd=_express-checkout" : window.location = url;
            window.paypalCheckoutReady = function() {
              paypal.checkout.setup("92654YYUEBVTN", {
                environment: 'sandbox'
            });
            paypal.checkout.initXO();
            $smh.support.cors = true;
            var url = paypal.checkout.urlPrefix +token;
            console.log('TEEEESSTT2: ' + url);
            paypal.checkout.startFlow(url);              
            }

//            var action = $smh.post('/set-express-checkout');
//
//            action.done(function (data) {
//                paypal.checkout.startFlow(url);
//            });
//
//            action.fail(function () {
//                paypal.checkout.closeFlow();
//            });

        },
        releaseDG: function (data) {
            if (data != undefined) {
                if (pptransact.check_for_html5_storage) {

                    pptransact.saveToLocalStorage(pptransact.getUserId(), data, null);

                    if (typeof pptransact.getSuccessBillCallBack() == 'function') {
                        pptransact.getSuccessBillCallBack().call(this, data);
                    }
                }

            } else {
                if (typeof pptransact.getFailBillCallBack == 'function') {
                    pptransact.getFailBillCallBack().call();
                }
            }
            dg.closeFlow();
        },
        check_for_html5_storage: function () {
            try {
                return 'localStorage' in window && window['localStorage'] !== null;
            } catch (e) {
                return false;
            }
        },
        saveToLocalStorage: function (userId, data, redirect) {
            var dataArray = $smh.parseJSON(localStorage.getItem(userId));

            if (!dataArray) {
                var dataArray = new Array();
                dataArray.push(data);
            } else {

                dataArray.push(data);
            }

            localStorage.setItem(userId, JSON.stringify(dataArray));

            if (redirect != null) {
                window.location.href = redirect;
            }

        },
        callServer: function (data, callbackFnk, failCallback) {
            $smh.ajax({
                url: pptransact.getUrl(),
                async: false,
                data: data,
                success: function (data) {
                    var obj = $smh.parseJSON(data);
                    if (!obj.success) {
                        failCallback.call();
                    }

                    if (typeof callbackFnk == 'function') {
                        callbackFnk.call(this, obj);
                    }
                },
                error: function (request, textStatus, error) {
                    failCallback.call(this, {
                        'request': request,
                        'status': textStatus,
                        'error': error
                    });
                }
            });
        }

    }
}();