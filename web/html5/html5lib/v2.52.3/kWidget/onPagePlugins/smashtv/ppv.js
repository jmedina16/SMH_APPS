kWidget.addReadyCallback( function( playerId ){
    window.kdp = $smh('#'+playerId).get(0); 
    var addOnce = false;
    var genClipListId = 'k-clipList-' + playerId;
    // remove any old genClipListId:
    $smh('#' + genClipListId ).remove();

    var ppv = function(kdp){
        return this.init(kdp);
    }
    ppv.prototype = {
        pluginName: 'ppv',
        init: function(kdp){
            this.kdp = kdp;
            this.pid = this.getConfig('pid');
            
            if(!blocked){
                kdp.addJsListener("freePreviewEnd", 'freePreviewEndHandler');                
            } else {
                if(!paid){
                    kdp.addJsListener("pluginsLoaded", "playerHandler");                   
                }
            }
            
            if(!livestream && !playlist && !category){
                kdp.addJsListener("playerUpdatePlayhead", "playerUpdatePlayheadHandler");                
            }            

            if(paid){
                kdp.addJsListener("playerPlayed", "playerPlayedHandler");
            } 
            
            if(media_type == 6){
                this.loadCat(); 
            }           
        },
        showPurchaseWindow: function(){ 
            kdp.sendNotification('doPause');
            var sessData;            
            var pid = this.getConfig('pid');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var sm_ak = this.getConfig('sm_ak');
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');
                        
            sessData = {
                pid: pid,
                sm_ak: sm_ak,
                entryId: entryId,
                type: type,
                protocol: protocol
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                data: sessData,
                dataType: 'json',
                beforeSend: function() {  
                    $smh('#smh_purchase_window').modal();
                    $smh('.modal-dialog').css('width','850px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function(msg) {
                if(!msg['success']){
                    $smh('.modal-dialog').css('width','600px');
                    $smh('.modal-content').html(msg['content']);
                } else {
                    if(msg['content']['success']){
                        $smh('.modal-dialog').css('width','850px');
                        $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Please make a ticket selection..</h4></div>\
                            <div id="ticket-login"><span id="smh-user"><a href="#" onclick="ppv_obj.login_form(\'' + entryId + '\');">Already registered? Login here</a></span></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        '+msg['content']['content']+'\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.registerForm(\'' + entryId + '\');">Register</button>\
                        </div>');
                    } else {
                        $smh('.modal-dialog').css('width','600px');
                        $smh('.modal-content').html(msg['content']['content']); 
                    }
                }
                refresh_player = true;
                $smh('#smh_purchase_window').on('hidden.bs.modal', function () {
                    if(refresh_player){
                        window.ppv.checkAccess(pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
                    }
                });
            });

        },
        registerForm: function(entryId){  
            var entry_ticket='';
            $smh("#smh_purchase_window").find(".nav-pills").each(function() {
                entry_ticket = $smh(this).find(".active a").attr('data-value');
            });
            
            var tmp = entry_ticket.split(",");
            window.smh_ppv_order['entry'] = tmp[1];
            window.smh_ppv_order['ticket'] = tmp[0];
            window.smh_ppv_order['type'] = tmp[2];
            window.smh_ppv_order['bill_per'] = tmp[3];
            
            $smh('.modal-dialog').css('width','370px');
            $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Registration</h4></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                            <form id="smh-register-form" action="">\
                            <div id="login-fail"></div>\
                            <table id="register-table" cellspacing="5" cellpadding="5" border="0">\
                                <tr><td><input type="text" name="fname" id="smh-fname" placeholder="First Name" /></td></tr>\
                                <tr><td><input type="text" name="lname" id="smh-lname" placeholder="Last Name" /></td></tr>\
                                <tr><td><input type="text" name="email" id="smh-email" placeholder="Email" /></td></tr>\
                                <tr><td><input type="text" name="email2" id="smh-email2" placeholder="Confirm Email" /></td></tr>\
                                <tr><td><div style="text-align: center;"><span style="margin-right: 20px; display: block; height: 20px;" id="loading"></span></div></td></tr>\
                            </table>\
                            </form>\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" onclick="ppv_obj.smh_back(\'' + entryId + '\'); return false;">Back</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.register(); return false;">Register</button>\
                        </div>');
            
            validator = $smh("#smh-register-form").validate({
                highlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },        
                unhighlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                rules:{
                    fname:{
                        required: true
                    },
                    lname:{
                        required: true
                    }, 
                    email:{
                        required: true,
                        email: true
                    },
                    email2:{
                        equalTo: '#smh-email'
                    }
                },
                messages: {
                    fname:{
                        required: "Please enter a first name"
                    },
                    lname:{
                        required: "Please enter a last name"
                    }, 
                    email:{
                        required: "Please enter a email",
                        email: "Please enter a valid email"
                    },
                    email2:{
                        equalTo: 'Emails do not match'
                    }
                }
            });                       
        },
        register: function(){
            var timezone = jstz.determine();
            var tz = timezone.name();
            var valid = validator.form();
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            if(valid){
                var fname = $smh('#smh-fname').val();
                var lname = $smh('#smh-lname').val();
                var email = $smh('#smh-email').val();

                var sessData = {
                    pid: pid,
                    sm_ak: sm_ak,
                    fname: fname,
                    lname: lname,
                    email: email,
                    tz: tz
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=register_account",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function() {                    
                        $smh('#loading').html('<img width="20px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function(data) {
                    if(data['success']){
                        ppv_obj.activateForm(email);
                    } else {
                        $smh('#login-fail').html('Registration failed. User already exists. Please try again.');
                        $smh('#login-fail').css({
                            'color':'#FF0000',
                            'font-size':'15px'
                        });
                        $smh('#loading').empty();
                    }
                });      
            }
        },
        activateForm: function(email){
            $smh('.modal-dialog').css('width','400px');
            $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><div class="alert alert-success">Thank you for registering! A confirmation email has been sent to <strong>'+email+'</strong>.<br> Please enter the activation key that has been email to you below.</div></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                            <form id="smh-activation-form" action="">\
                            <table id="activate-table" cellspacing="5" cellpadding="5" border="0">\
                                <tr><td><input type="text" name="akey" id="akey" placeholder="Activation Key" /></td></tr>\
                                <tr><td><div style="text-align: center;"><span style="margin-right: 20px; display: block; height: 20px;"></span></div></td></tr>\
                            </table>\
                            </form>\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.activate(\'' + email + '\'); return false;">Activate</button>\
                        </div>');
            
            validator = $smh("#smh-activation-form").validate({
                highlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },        
                unhighlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                rules:{
                    akey:{
                        required: true
                    }
                },
                messages: {
                    akey:{
                        required: "Please enter your activation key"
                    }
                }
            }); 
        },
        activate: function(email){
            var valid = validator.form();
            var timezone = jstz.determine();
            var tz = timezone.name();
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            if(valid){
                var akey = $smh('#akey').val();
                var sessData = {
                    pid: pid,
                    sm_ak: sm_ak,
                    akey: akey,
                    tz: tz,
                    email: email
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=activate_account",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function() {                    
                        $smh('#loading').html('<img width="20px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function(data) {
                    if(data['success']){
                        $smh('.modal-dialog').css('width','400px');
                        $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><div class="alert alert-success">Activation was successful! Click on <strong>continue</strong> to complete your purchase.</div></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.confirm(); return false;">Continue</button>\
                        </div>');
                        $smh.cookie('smh_auth_key', data['auth_key']);
                        window.smh_ppv_order['userId'] = data['user_id'];
                    } else {
                        $smh('#ticket-instructions').html('<div class="alert alert-danger">Activation failed. Please try again.</div>');
                        $smh('#ticket-instructions').css('float','none');
                        $smh('#loading').empty();
                    }
                });    
            }            
        },
        confirm: function(){
            var type = this.getConfig('type');
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entryId: this.getConfig('entryId'),
                ticket_id: window.smh_ppv_order['ticket'],
                type: type,
                protocol: protocol
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_confirm",
                data: sessData,
                dataType: 'json',
                beforeSend: function() {  
                    $smh('#smh_purchase_window').modal();
                    $smh('.modal-dialog').css('width','850px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function(msg) {
                if(msg['success']){
                    $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Confirm</h4></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        '+msg['content']+'\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.pay();">Confirm and Pay</button>\
                        </div>');   
                } else {
                    $smh('.modal-content').html('<div style="height: 55px; margin-top: 30px; width: 291px; margin-left: auto; margin-right: auto;">Something went wrong. Please try again later.</div>\
                                <div class="modal-footer">\
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                         </div>');
                }
            });           
        },
        loggedin_confirm: function(){
            var entry_ticket='';
            $smh("#smh_purchase_window").find(".nav-pills").each(function() {
                entry_ticket = $smh(this).find(".active a").attr('data-value');
            });
            
            var tmp = entry_ticket.split(",");
            window.smh_ppv_order['entry'] = tmp[1];
            window.smh_ppv_order['ticket'] = tmp[0];
            window.smh_ppv_order['type'] = tmp[2];
            window.smh_ppv_order['bill_per'] = tmp[3];
            
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entryId: this.getConfig('entryId'),
                ticket_id: window.smh_ppv_order['ticket'],
                type: this.getConfig('type'),
                protocol: protocol
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_confirm",
                data: sessData,
                dataType: 'json',
                beforeSend: function() {  
                    $smh('#smh_purchase_window').modal();
                    $smh('.modal-dialog').css('width','850px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function(msg) {
                if(msg['success']){
                    $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Confirm</h4></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        '+msg['content']+'\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.pay();">Confirm and Pay</button>\
                        </div>');   
                } else {
                    $smh('.modal-content').html('<div style="height: 55px; margin-top: 30px; width: 291px; margin-left: auto; margin-right: auto;">Something went wrong. Please try again later.</div>\
                                <div class="modal-footer">\
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                         </div>');
                }
            });           
        },
        pay: function(){
            var timezone = jstz.determine();
            var tz = timezone.name();
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entry_id: window.smh_ppv_order['entry'],
                user_id: window.smh_ppv_order['userId'],
                ticket_id: window.smh_ppv_order['ticket'],
                tz: tz,
                media_type: media_type
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=add_order",
                data: sessData,
                dataType: 'json',
                beforeSend: function() {  
                    $smh('#smh_purchase_window').modal();
                    $smh('.modal-dialog').css('width','850px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function(msg) {
                if(msg['success']){
                    ppv_obj.paypal(msg['order_id'], msg['sub_id']);  
                }                
            });
        },
        paypal: function(order_id, sub_id){
            var userId = window.smh_ppv_order['userId'];
            var entryId = window.smh_ppv_order['entry'];
            var ticketId = window.smh_ppv_order['ticket'];
            var ticket_type = window.smh_ppv_order['type'];
            var bill_per = window.smh_ppv_order['bill_per'];
            var kentry = this.getConfig('entryId');
            var type = this.getConfig('type');
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            window.pptransact.init(protocol,false);
            window.pptransact.bill({
                userId: userId,
                entryId: entryId,
                ticketId: ticketId,
                ticket_type: ticket_type,
                bill_per: bill_per,
                kentry: kentry,
                orderId: order_id,
                subId: sub_id,
                itemQty:'1',
                pid: pid,
                sm_ak: sm_ak,
                type: type,
                protocol: protocol,
                successCallback: function(ret) {
                    //bill success
                    var timezone = jstz.determine();
                    var tz = timezone.name();
                    var sessData = {
                        pid: pid,
                        sm_ak: sm_ak,
                        entry_id: entryId,
                        user_id: userId,
                        ticket_id: ticketId,
                        ticket_type: ticket_type,
                        order_id: order_id,
                        tz: tz,
                        type: type,
                        media_type: media_type,
                        smh_aff: smh_aff,
                        payment_status: ret.paymentStatus                            
                    }
                    if(ret.paymentStatus == 'Completed'){
                        $smh.ajax({
                            type: "GET",
                            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=complete_order",
                            data: sessData,
                            dataType: 'json'
                        }).done(function(msg) {
                            if(msg['success']){
                                ppv_obj.checkUserInventory(pid,sm_ak,userId,kentry,type); 
                            } else {
                                $smh('.modal-content').html('<div style="height: 55px; margin-top: 30px; width: 291px; margin-left: auto; margin-right: auto; text-align: center;">\
                                    <strong>Error: </strong>Something went wrong. Please contact the website administrator.</div>\
                                    <div class="modal-footer">\
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                                    </div>');   
                            }    
                        });
                    } else {
                        $smh.ajax({
                            type: "GET",
                            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=finish_order",
                            data: sessData,
                            dataType: 'json'
                        }).done(function(msg) {
                            if(msg['success']){
                                $smh('.modal-content').html('<div style="height: 87px; margin-top: 30px; width: 420px; margin-left: auto; margin-right: auto; text-align: center;">\
                                    <strong>Notice: </strong>Your payment was not completed.<br>\
                                    The following payment status was returned: <strong>'+msg['status']+'</strong><br>\
                                    You will receive an email once the payment status changes to <strong>completed</strong>.\
                                    </div>\
                                    <div class="modal-footer">\
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                                    </div>'); 
                            } else {
                                $smh('.modal-content').html('<div style="height: 55px; margin-top: 30px; width: 291px; margin-left: auto; margin-right: auto;">\
                                    <strong>Error: </strong>Something went wrong. Please contact the website administrator.</div>\
                                    <div class="modal-footer">\
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                                    </div>');
                            }               
                        });
                    }
                },
                failCallback: function(ret) {
                //bill canceled
                }
            });
        },
        showLoggedInPurchaseWindow: function(){
            kdp.sendNotification('doPause');
            var sessData;            
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');

            sessData = {
                pid: pid,
                sm_ak: sm_ak,
                entryId: entryId,
                type: type,
                protocol: protocol
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                data: sessData,
                dataType: 'json',
                beforeSend: function() {  
                    $smh('#smh_purchase_window').modal();
                    $smh('.modal-dialog').css('width','850px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function(msg) {
                if(!msg['success']){
                    $smh('.modal-dialog').css('width','600px');
                    $smh('.modal-content').html(msg['content']);
                } else {
                    if(msg['content']['success']){
                        window.smh_ppv_order['userId'] = userId;
                        var sessData = {
                            uid: userId,
                            pid: pid,
                            sm_ak: sm_ak
                        }
                        $smh.ajax({
                            type: "GET",
                            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_user_name",
                            data: sessData,
                            dataType: 'json'
                        }).done(function(data) {
                            refresh_player = true;
                            is_logged_in = true;
                            $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Please make a ticket selection..</h4></div>\
                            <div id="ticket-login"><span id="smh-user" style="color: #666; font-size: 13px;">Welcome, '+data+'<br /><a href="#" onclick="ppv_obj.smhLogout(\''+entryId+'\'); return false;">Sign Out</a></span></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        '+msg['content']['content']+'\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.loggedin_confirm();">Continue</button>\
                        </div>');
                            $smh('#smh_purchase_window').on('hidden.bs.modal', function () {
                                if(refresh_player){
                                    window.ppv.checkAccess(pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);                                                      
                                }
                            });
                        });
                    } else {
                        $smh('.modal-dialog').css('width','600px');
                        $smh('.modal-content').html(msg['content']['content']);
                    }
                }
            });
        },
        showCurrentTime: function(data, id){
            current_time = data;
        },
        login_form: function(entryId){
            $smh('.modal-dialog').css('width','435px');
            $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Sign In</h4></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                            <form id="smh-login-form" action="">\
                                <div id="login-fail"></div>\
                                <table id="ticket-login-table" cellspacing="5" cellpadding="5" border="0">\
                                    <tr><td><input type="text" name="username" id="smh-username" placeholder="Email" /></td></tr>\
                                    <tr><td><input type="password" name="pass" id="smh-pass" placeholder="Password" /></td></tr>\
                                    <tr><td><div style="text-align: center;"><span style="margin-right: 20px; display: block; height: 20px;" id="loading"></span></div></td></tr>\
                                </table>\
                            </form>\
                        </div>\
                        <div class="modal-footer">\
                            <div id="pass_rec" style="float: left;"><a href="#" onclick="ppv_obj.smh_password_form(\'' + entryId + '\'); return false;">Forgot Password?</a></div>\
                            <button type="button" class="btn btn-default" onclick="ppv_obj.smh_back(\'' + entryId + '\'); return false;">Back</button>\
                            <button type="button" class="btn btn-primary" id="signin" onclick="ppv_obj.smhLogin(); return false;">Sign In</button>\
                        </div>');

            validator = $smh("#smh-login-form").validate({
                highlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },        
                unhighlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                rules:{
                    username:{
                        required: true,
                        email: true
                    },   
                    pass:{
                        required: true
                    }
                },
                messages: {
                    username:{
                        required: "Please enter a email",
                        email: "Please enter a valid email"
                    },
                    pass:{
                        required: "Please enter a password"
                    }
                }
            });
        },
        smhLogin: function(){
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var valid = validator.form();
            if(valid){
                var username = $smh('#smh-username').val();
                var password = $smh('#smh-pass').val();
                var sessData = {
                    pid: this.getConfig('pid'),
                    sm_ak: sm_ak,
                    un: username,
                    pswd: password
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=login_user",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function() {                    
                        $smh('#loading').html('<img width="20px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function(data) {
                    if(data['success']){
                        $smh('#loading').empty();
                        $smh.cookie('smh_auth_key', data['auth_key']);
                        window.smh_ppv_order['userId'] = data['user_id'];
                        ppv_obj.checkUserInventory(pid,sm_ak,data['user_id'],entryId,type);
                        
                    }else{
                        $smh('#login-fail').html('No account found with the given email and password.');
                        $smh('#login-fail').css({
                            'color':'#FF0000',
                            'font-size':'12px'
                        });
                        $smh('#loading').empty();
                    }
                        
                });
            }
        },
        smhLogout: function(entryId){
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');
            var type = this.getConfig('type');
            $smh.removeCookie('smh_auth_key');
            $smh('#smh_purchase_window').modal('hide');
            is_logged_in = false;
            refresh_player = false;
            window.ppv.loadVideo('',pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
        },
        smh_password_form: function(entryId){           
            $smh('.modal-dialog').css('width','370px');
            $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><div class="alert alert-info" style="font-size: 12px;"><h4 class="modal-title">Password Recovery</h4>You can use this form to reset your password if you have forgotten it. Enter your email address below to get started.</div></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                            <form id="smh-password-form" action="">\
                            <div id="pass-fail"></div>\
                            <table id="pass-table" cellspacing="5" cellpadding="5" border="0">\
                                <tr><td><input type="text" name="email" id="smh-email" placeholder="Email" /></td></tr>\
                                <tr><td><div style="text-align: center;"><span style="margin-right: 20px; display: block; height: 20px;" id="loading"></span></div></td></tr>\
                            </table>\
                            </form>\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" onclick="ppv_obj.login_form(\'' + entryId + '\'); return false;">Back</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.pass_reset_form(\'' + entryId + '\'); return false;">Submit</button>\
                        </div>');
            
            validator = $smh("#smh-password-form").validate({
                highlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },        
                unhighlight: function(element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                rules:{
                    email:{
                        required: true,
                        email: true
                    }
                },
                messages: { 
                    email:{
                        required: "Please enter a email",
                        email: "Please enter a valid email"
                    }
                }
            });
        },
        pass_reset_form: function(entryId){
            var valid = validator.form();
            if(valid){
                var email = $smh('#smh-email').val();
                var sm_ak = this.getConfig('sm_ak');
                var sessData = {
                    pid: this.getConfig('pid'),
                    sm_ak: sm_ak,
                    email: email
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=reset_request",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function() {                    
                        $smh('#loading').html('<img width="20px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function(data) {
                    if(data['success']){
                        $smh('.modal-dialog').css('width','400px');
                        $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><div class="alert alert-success">Your password reset request has been submitted. An email has been sent to <strong>'+email+'</strong> with your reset token. Please enter your reset token below.</div></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                            <form id="smh-reset-form" action="">\
                            <table id="reset-table" cellspacing="5" cellpadding="5" border="0">\
                                <tr><td><input type="text" name="reset_token" id="reset_token" placeholder="Reset Token" /></td></tr>\
                                <tr><td><div style="text-align: center;"><span style="margin-right: 20px; display: block; height: 20px;" id="loading"></span></div></td></tr>\
                            </table>\
                            </form>\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.pass_reset(\'' + email + '\',\'' + entryId + '\'); return false;">Reset</button>\
                        </div>');
            
                        validator = $smh("#smh-reset-form").validate({
                            highlight: function(element, errorClass) {
                                $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                            },        
                            unhighlight: function(element, errorClass) {
                                $smh(element).removeClass("valid").removeClass("validate-error");
                            },
                            rules:{
                                reset_token:{
                                    required: true
                                }
                            },
                            messages: {
                                reset_token:{
                                    required: "Please enter your reset token"
                                }
                            }
                        });                         
                    }                        
                });                             
            }
        },
        pass_reset: function(email,entryId){
            var valid = validator.form();
            if(valid){
                var reset_token = $smh('#reset_token').val();
                var sm_ak = this.getConfig('sm_ak');
                var sessData = {
                    pid: this.getConfig('pid'),
                    sm_ak: sm_ak,
                    email: email,
                    reset_token: reset_token
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=reset_pass",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function() {                    
                        $smh('#loading').html('<img width="20px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function(data) {
                    if(data['success']){
                        $smh('.modal-dialog').css('width','400px');
                        $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><div class="alert alert-success">Your password was successfully reset! Your new password has been emailed to <strong>'+email+'</strong>.</div></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.login_form(\'' + entryId + '\'); return false;">Login</button>\
                        </div>');
                    } else {
                        $smh('#ticket-instructions').html('<div class="alert alert-danger">Password reset failed. Please try again.</div>');
                        $smh('#ticket-instructions').css('float','none');
                        $smh('#loading').empty();
                    }
                });
            }
        },
        checkUserInventory: function(pid,sm_ak,uid,entryId,type){
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');
            
            var sessData = {
                entryId: entryId,
                uid: uid,
                pid: pid,
                sm_ak: sm_ak,
                type: type
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=check_inventory",
                data: sessData,
                dataType: 'json'
            }).done(function(data) {
                if(!data){
                    var sessData = {
                        uid: uid,
                        pid: pid,
                        sm_ak: sm_ak
                    }
                    $smh.ajax({
                        type: "GET",
                        url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_user_name",
                        data: sessData,
                        dataType: 'json'
                    }).done(function(data) {
                        var sessData = {
                            pid: pid,
                            sm_ak: sm_ak,
                            entryId: entryId,
                            type: type,
                            protocol: protocol
                        }
                        $smh.ajax({
                            type: "GET",
                            url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                            data: sessData,
                            dataType: 'json',
                            beforeSend: function() {  
                                $smh('#smh_purchase_window').modal();
                                $smh('.modal-dialog').css('width','850px');
                                $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                            }
                        }).done(function(msg) {
                            if(!msg['success']){
                                $smh('.modal-dialog').css('width','600px');
                                $smh('.modal-content').html(msg['content']);
                            } else {
                                if(msg['content']['success']){
                                    $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Please make a ticket selection..</h4></div>\
                            <div id="ticket-login"><span id="smh-user" style="color: #666; font-size: 13px;">Welcome, '+data+'<br /><a href="#" onclick="ppv_obj.smhLogout(\''+entryId+'\'); return false;">Sign Out</a></span></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        '+msg['content']['content']+'\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.loggedin_confirm();">Continue</button>\
                        </div>');
                                    is_logged_in = true;
                                    userId = uid;
                                } else {
                                    $smh('.modal-dialog').css('width','600px');
                                    $smh('.modal-content').html(msg['content']['content']);
                                }
                            }
                        });                      
                    });

                } else {
                    paid = true;
                    kdp.addJsListener("playerPlayed", "playerPlayedHandler");
                    is_logged_in = true;
                    userId = uid;
                    $smh('.modal-dialog').css('width','850px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');                    
                    
                    if(!playlist && !category){
                        kdp.setKDPAttribute( 'servicesProxy.kalturaClient', 'ks', data );                        
                        kdp.sendNotification('cleanMedia');                  
                        kdp.sendNotification('changeMedia', {
                            'entryId': entryId
                        });
                    
                        if(!livestream){
                            kdp.addJsListener('playerSeekEnd', 'onDoSeek' );                    
                            setTimeout(function(){
                                kdp.sendNotification('doSeek', current_time);
                            },2000);
                        }                    
                    }                    
                    
                    setTimeout(function(){   
                        refresh_player = false;
                        
                        if(!playlist && !category){
                            kdp.sendNotification( 'doPlay' );
                        } else if(playlist || category){
                            init_loaded = false;
                            window.ppv.loadVideo(data,pid,sm_ak,uiconf_id,uiconf_width,uiconf_height,entryId,type);
                        }                 

                        $smh('#smh_purchase_window').modal('hide');
                    },5000);  
                }                                
            });  
        },
        smh_back: function(entryId){
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                type: this.getConfig('type'),
                entryId: entryId,
                protocol: protocol
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                data: sessData,
                dataType: 'json',
                beforeSend: function() {  
                    $smh('#smh_purchase_window').modal();
                    $smh('.modal-dialog').css('width','850px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="'+protocol+'://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.17/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function(msg) {
                if(!msg['success']){
                    $smh('.modal-dialog').css('width','600px');
                    $smh('.modal-content').html(msg['content']);
                } else {
                    if(msg['content']['success']){
                        $smh('.modal-dialog').css('width','850px');
                        $smh('.modal-content').html('<div class="modal-header">\
                            <div id="ticket-instructions"><h4 class="modal-title">Please make a ticket selection..</h4></div>\
                            <div id="ticket-login"><span id="smh-user"><a href="#" onclick="ppv_obj.login_form(\'' + entryId + '\');">Already registered? Login here</a></span></div>\
                            <div class="clear"></div>\
                        </div>\
                        <div class="modal-body">\
                        '+msg['content']['content']+'\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            <button type="button" class="btn btn-primary" onclick="ppv_obj.registerForm(\'' + entryId + '\');">Register</button>\
                        </div>');
                    } else {
                        $smh('.modal-dialog').css('width','600px');
                        $smh('.modal-content').html(msg['content']['content']); 
                    }
                }
            });
        },
        updateView: function(){
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entryId: this.getConfig('entryId'),
                uid: userId
            }
            
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=update_user_views",
                data: sessData,
                dataType: 'json'
            });
        },    
        getClipListTarget: function(){
            // check for generated id:
            if( $smh('#' + genClipListId ).length ){
                return  $smh('#' + genClipListId );
            }
            var clipListId = ppv_obj.getConfig('clipListTargetId');
            if( clipListId == "null" ){
                clipListId = null;
            }
            // check for clip target:
            if( clipListId && $smh('#' + clipListId ).length ){
                return  $smh('#' + clipListId)
            }
            // Generate a new clip target ( if none was found )           
            var layout = ppv_obj.getConfig('layoutMode');
            
            if(layout == 'top'){
                return $smh('<div />').attr('id', genClipListId ).insertBefore(  $smh( '#' + playerId ) );
            } else {
                return $smh('<div />').attr('id', genClipListId ).insertAfter(  $smh( '#' + playerId ) );
            }
        },
        activateEntry: function(activeEntryId){
            var $carousel = ppv_obj.getClipListTarget().find( '.k-carousel' );
            // highlight the active clip ( make sure only one clip is highlighted )
            var $clipList = ppv_obj.getClipListTarget().find( 'ul li' );
            ;
            if( $clipList.length && activeEntryId ){
                $clipList.each( function( inx, clipLi ){
                    // kdp moves entryId to .entryId in playlist data provider ( not a db mapping )
                    var entryMeta =  $smh( clipLi ).data( 'entryMeta' );
                    var clipEntryId = entryMeta;
                    if( clipEntryId == activeEntryId ){
                        $smh( clipLi ).addClass( 'k-active' ).data( 'activeEntry', true );

                        // scroll to the target entry ( if not already shown ):
                        if( inx == 0 || ppv_obj.getClipListTarget().find('ul').width() > ppv_obj.getClipListTarget().width() ){
                            $carousel[0].jCarouselLiteGo( inx );
                        }
                    } else {
                        $smh( clipLi ).removeClass( 'k-active' ).data('activeEntry', false)
                    }
                });
            }
        },
        loadCat: function(){            
            kdp.kBind( "changeMedia.onPagePlaylist", function( clip ){        
                ppv_obj.activateEntry( clip['entryId'] );
            });
    
            kdp.kBind( "mediaReady", function(){
                var pid = ppv_obj.getConfig('pid');
                var sm_ak = ppv_obj.getConfig('sm_ak');
            
                if( addOnce ){
                    return ;
                }
                var clipListId = ppv_obj.getConfig('clipListTargetId');
                if( clipListId == "null" ){
                    clipListId = null;
                }
                addOnce = true; 

                // check for a target
                $clipListTarget = ppv_obj.getClipListTarget();
                // Add a base style class:
                $clipListTarget.addClass( 'kWidget-clip-list' );

                // add layout mode:
                var layoutMode = ppv_obj.getConfig('layoutMode') || 'right';
                $clipListTarget.addClass( 'k-' + layoutMode );

                // get the thumbWidth:
                var thumbWidth =  ppv_obj.getConfig('thumbWidth') || '110';
                // standard 3x4 box ratio:
                var thumbHeight = thumbWidth*.75;

                // calculate how many clips should be visible per size and cliplist Width
                var clipsVisible = null;
                var liSize = {};
		
                // check layout mode:
                var isLeft = ( layoutMode == 'left' );
                var isRight = ( layoutMode == 'right' );
                var isBottom = ( layoutMode == 'bottom' );
                var isTop = ( layoutMode == 'top' );
                if( isRight ){
                    // Give player height if dynamically added:
                    if( !clipListId ){
                        // if adding in after the player make sure the player is float left so
                        // the playlist shows up after:
                        $smh(kdp).css('float', 'left');
                        $clipListTarget
                        .css( {
                            'float' : 'left',
                            'padding-left' : '5px',
                            'height' : $smh( kdp ).height() + 'px',
                            'width' : $smh( kdp ).width() + 'px'
                        });
                    }

                    clipsVisible = Math.floor( $clipListTarget.height() / ( parseInt( thumbHeight ) + 4 ) );
                    liSize ={
                        'width' : '100%',
                        'height': thumbHeight
                    };
                } else if( isLeft ){
                    // Give player height if dynamically added:
                    if( !clipListId ){
                        // if adding in after the player make sure the player is float left so
                        // the playlist shows up after:
                        $smh(kdp).css('float', 'right');
                        $clipListTarget
                        .css( {
                            'float' : 'right',
                            'padding-right' : '5px',
                            'height' : $smh( kdp ).height() + 'px',
                            'width' : $smh( kdp ).width() + 'px'
                        });
                    }

                    clipsVisible = Math.floor( $clipListTarget.height() / ( parseInt( thumbHeight ) + 4 ) );
                    liSize ={
                        'width' : '100%',
                        'height': thumbHeight
                    };
                } else if(isTop) {
                    // horizontal layout
                    // Give it player width if dynamically added:
                    if( !clipListId ){
                        $clipListTarget.css({
                            'margin-bottom' : '5px',
                            'width' : $smh( kdp ).width() + 'px',
                            'height' : thumbHeight
                        });
                    }
                    clipsVisible = Math.floor( $clipListTarget.width() / ( parseInt( thumbWidth ) + 4 ) );
                    liSize = {
                        'width': thumbWidth,
                        'height': thumbHeight
                    };
                } else if(isBottom) {
                    // horizontal layout
                    // Give it player width if dynamically added:
                    if( !clipListId ){
                        $clipListTarget.css({
                            'padding-top' : '5px',
                            'width' : $smh( kdp ).width() + 'px',
                            'height' : thumbHeight
                        });
                    }
                    clipsVisible = Math.floor( $clipListTarget.width() / ( parseInt( thumbWidth ) + 4 ) );
                    liSize = {
                        'width': thumbWidth,
                        'height': thumbHeight
                    };
                }
		
                var $clipsUl = $smh('<ul>').css({
                    "height": '100%'
                })
                .appendTo( $clipListTarget )
                .wrap(
                    $smh( '<div />' ).addClass('k-carousel')
                    )
		
                // append all the clips
                init_clip = false;
                var first_clip = '';
                $smh.each( cat_entries, function( inx, clip ){
                        
                    if(!init_clip){
                        first_clip = clip['entry_id'];                            
                        init_clip = true;   
                    }
                        
                    $clipsUl.append(
                        $smh('<li />')
                        .css( liSize )
                        .data( {
                            'entryMeta': clip['entry_id'],
                            'index' : inx
                        })
                        .append(
                            $smh('<img />')
                            .attr({
                                'src' : protocol+'://mediaplatform.streamingmediahosting.com/p/'+pid+'/thumbnail/entry_id/'+clip['entry_id']+ '/width/' + thumbWidth
                            }),
                            $smh('<div />')
                            .addClass( 'k-clip-desc' )
                            .append(
                                $smh('<h3 />')
                                .addClass( 'k-title' )
                                .text( clip['name'] ),

                                $smh('<p />')
                                .addClass( 'k-description' )
                                .text( ( clip['desc'] == null ) ? '': clip['desc'] )
                                )
                            )
                        .click(function(){                 
                            ppv_obj.checkCatInvetory(pid,sm_ak,userId,clip['entry_id']);
                        }).hover(function(){
                            $smh( this ).addClass( 'k-active' );
                        },
                        function(){
                            // only remove if not the active entry:
                            if( !$smh( this ).data( 'activeEntry' ) ){
                                $smh( this ).removeClass( 'k-active' );
                            }
                        })
                        )
                });

                // Add scroll buttons
                $clipListTarget.prepend(
                    $smh( '<a />' )
                    .addClass( "k-scroll k-prev" )
                    )
                $clipListTarget.append(
                    $smh( '<a />' )
                    .addClass( "k-scroll k-next" )
                    )
                // don't show more clips then we have available 
                if( clipsVisible > cat_entries.length ){
                    clipsVisible = cat_entries.length;
                }
		
                // Add scrolling carousel to clip list ( once dom sizes are up-to-date )
                var verical = false;
                
                if(isLeft || isRight){
                    verical = true;
                }
                
                $clipListTarget.find( '.k-carousel' ).jCarouselLite({
                    btnNext: ".k-next",
                    btnPrev: ".k-prev",
                    visible: clipsVisible,
                    mouseWheel: true,
                    circular: false,
                    vertical: verical
                });
                // test if k-carousel is too large for scroll buttons:
                if( !verical && $clipListTarget.find( '.k-carousel' ).width() > $clipListTarget.width() - 40 ){
                    $clipListTarget.find( '.k-carousel' ).css('width',
                        $clipListTarget.width() - 40
                        )
                }

                // sort ul elements:
                $clipsUl.find('li').sortElements(function(a, b){
                    return $smh(a).data('index') > $smh(b).data('index') ? 1 : -1;
                });
                    
                ppv_obj.activateEntry(first_clip);             
            });             
        },
        checkCatInvetory: function(pid,sm_ak,uid,entryId){
            kdp.sendNotification( 'doPause' );
            var sessData = {
                entryId: entryId,
                pid: pid,
                sm_ak: sm_ak,
                access: paid
            }
            $smh.ajax({
                type: "GET",
                url: protocol+"://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=check_cat_inventory",
                data: sessData,
                dataType: 'json'
            }).done(function(data) {
                if(!data){
                    kdp.sendNotification('changeMedia', {
                        'entryId': entryId
                    });
                } else {
                    kdp.setKDPAttribute( 'servicesProxy.kalturaClient', 'ks', data );
                    kdp.sendNotification('changeMedia', {
                        'entryId': entryId
                    });
                    paid = true;
                }                                
            }); 
        },
        normalizeAttrValue: function( attrValue ){
            // normalize flash kdp string values
            switch( attrValue ){
                case "null":
                    return null;
                    break;
                case "true":
                    return true;
                    break;
                case "false":
                    return false;
                    break;
            }
            return attrValue;
        },
        getAttr: function( attr ) {
            return this.normalizeAttrValue(
                kdp.evaluate( '{' + attr + '}' )
                );
        },
        getConfig : function( attr ){
            return this.getAttr(this.pluginName + '.' + attr);
        }
    }  
    window['freePreviewEndHandler'] = function(){
        if(is_logged_in){
            ppv_obj.showLoggedInPurchaseWindow();
        } else {
            ppv_obj.showPurchaseWindow();  
        }        
    }
    
    window['playerUpdatePlayheadHandler'] = function(data, id){
        ppv_obj.showCurrentTime(data, id);
    }   
    
    window['onDoSeek'] = function(){
        kdp.sendNotification( 'doPlay' );
        setTimeout(function(){
            kdp.sendNotification( 'doPlay' );
        },1000);
    };
    
    window['playerPlayedHandler'] = function(){
        if(is_logged_in && !init_loaded){
            init_loaded = true;
            ppv_obj.updateView();
        }
    }
    
    window['playerHandler'] = function(){
        if(is_logged_in){
            ppv_obj.showLoggedInPurchaseWindow();
        } else {
            ppv_obj.showPurchaseWindow();  
        }  
    }  
    
    ppv_obj = new ppv(kdp); 
   
});

if(!init_loaded){
    window.$smh('body').append('<div class="modal fade" id="smh_purchase_window" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\
                <div class="modal-dialog">\
                    <div class="modal-content">\
                    </div>\
                </div>\
            </div>');   
}

function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;
	
    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";
	
    if(typeof(arr) == 'object') { //Array/Hashes/Objects 
        for(var item in arr) {
            var value = arr[item];
			
            if(typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}
