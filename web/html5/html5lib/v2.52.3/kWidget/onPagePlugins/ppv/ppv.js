kWidget.addReadyCallback(function (playerId) {
    window.kdp = $smh('#' + playerId).get(0);
    var addOnce = false;
    var genClipListId = 'k-clipList-' + playerId;
    // remove any old genClipListId:
    $smh('#' + genClipListId).remove();

    var ppv = function (kdp) {
        return this.init(kdp);
    }
    ppv.prototype = {
        pluginName: 'ppv',
        init: function (kdp) {
            this.kdp = kdp;
            this.pid = this.getConfig('pid');
            if (!blocked) {
                kdp.addJsListener("freePreviewEnd", 'freePreviewEndHandler');
            }

            if (paid) {
                kdp.addJsListener("playerPlayed", "playerPlayedHandler");
                kdp.addJsListener("playerPaused", "playerPausedHandler");
            }

            if (media_type == 6) {
                this.loadCat();
            }
        },
        showPurchaseWindow: function () {
            kdp.sendNotification('doPause');
            var sessData;
            var pid = this.getConfig('pid');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var sm_ak = this.getConfig('sm_ak');

            sessData = {
                pid: pid,
                sm_ak: sm_ak,
                entryId: entryId,
                type: type,
                protocol: protocol,
                logged_in: is_logged_in,
                has_start: (start_date) ? true : false
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#smh_purchase_window').modal({
                        backdrop: 'static'
                    });
                    $smh('.modal-dialog').css('width', '400px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function (msg) {
                ppv_obj.resetModal();
                if (!msg['success']) {
                    $smh('.modal-content').html(msg['content']);
                } else {
                    if (msg['content']['success']) {
                        var header, content;
                        header = '<button type="button" class="close purchase-close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Tickets</h4>';
                        $smh('#smh_purchase_window .modal-header').html(header);

                        var entry_desc = '';
                        if (msg['content']['desc']) {
                            entry_desc = '<div id="entry-desc">' + msg['content']['desc'] + '</div>';
                        }
                        content = entry_desc +
                                '<div id="ticket-wrapper">' + msg['content']['tickets'] + '</div>' +
                                '<div class="clear"></div>';

                        $smh('#smh_purchase_window .modal-body').html(content);
                    } else {
                        $smh('.modal-dialog').css('width', '600px');
                        $smh('.modal-content').html(msg['content']['content']);
                    }
                }
            });

        },
        register_window: function () {
            ppv_obj.resetModal();
            var header, content;
            var additional_attr = '';
            $smh('#smh_purchase_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '398px');
            header = '<span style="font-size: 15px;">Register</span><button type="button" class="close" onclick="ppv_obj.destroyToolTipRegisterLogin();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button>';
            $smh('#smh_purchase_window .modal-header').html(header);

            if (owner_attr) {
                $smh.each(owner_attr, function (index, value) {
                    additional_attr += '<div class="form-group">' +
                            '<div class="input-group">' +
                            '<div class="input-group-addon"><i class="fa fa-user"></i></div>' +
                            '<input type="text" name="attr' + value.id + '" id="smh-attr' + value.id + '" class="form-control" placeholder="' + value.name + '" />' +
                            '</div>' +
                            '</div>';
                });
            }

            content = '<form id="smh-register-form" action="">' +
                    '<div id="register-fail"></div>' +
                    '<div id="register-table">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-user"></i></div>' +
                    '<input type="text" name="fname" id="smh-fname" class="form-control" placeholder="First Name" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-user"></i></div>' +
                    '<input type="text" name="lname" id="smh-lname" class="form-control" placeholder="Last Name" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="email" id="smh-email" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-lock"></i></div>' +
                    '<input type="password" name="registerPass" id="smh-register-pass" class="form-control" placeholder="Password" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-lock"></i></div>' +
                    '<input type="password" name="registerPass2" id="smh-register-pass2" class="form-control" placeholder="Confirm Password" />' +
                    '</div>' +
                    '</div>' +
                    additional_attr +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" id="register-button" style="float: right; width: 95px;" onclick="ppv_obj.register(false); return false;">Register</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right;" id="register-loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>';

            $smh('#smh_purchase_window .modal-body').html(content);

            $smh('#smh-register-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            register_validator = $smh("#smh-register-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    fname: {
                        required: true
                    },
                    lname: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    registerPass: {
                        required: true,
                        mypassword: true,
                        minlength: 5
                    },
                    registerPass2: {
                        required: true,
                        minlength: 5,
                        equalTo: '#smh-register-pass'
                    }
                },
                messages: {
                    fname: {
                        required: "Please enter a first name"
                    },
                    lname: {
                        required: "Please enter a last name"
                    },
                    email: {
                        required: "Please enter an email",
                        email: "Please enter a valid email"
                    },
                    registerPass: {
                        required: "Please enter a password",
                        minlength: "Your password must be at least 5 characters long"
                    },
                    registerPass2: {
                        required: "Please enter a password",
                        minlength: "Your password must be at least 5 characters long",
                        equalTo: 'Passwords do not match'
                    }
                }
            });

            if (owner_attr) {
                $smh.each(owner_attr, function (index, value) {
                    if ((value.required == '1')) {
                        $smh("#smh-attr" + value.id).rules("add", {
                            required: true,
                            messages: {
                                required: "This field is required"
                            }
                        });
                    }
                });
            }
        },
        register_loggin: function (ticket, entry, type, bill_per, entry_id) {
            ppv_obj.resetModal();
            var header, content;
            var additional_attr = '';
            window.smh_ppv_order['ticket'] = ticket;
            window.smh_ppv_order['entry'] = entry;
            window.smh_ppv_order['type'] = type;
            window.smh_ppv_order['bill_per'] = bill_per;

            $smh('#smh_purchase_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '398px');

            header = '<span style="font-size: 15px;">Please log in first or register an account</span><button type="button" class="close" onclick="ppv_obj.destroyToolTipRegisterLogin();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button>';
            $smh('#smh_purchase_window .modal-header').html(header);

            if (owner_attr) {
                $smh.each(owner_attr, function (index, value) {
                    additional_attr += '<div class="form-group">' +
                            '<div class="input-group">' +
                            '<div class="input-group-addon"><i class="fa fa-user"></i></div>' +
                            '<input type="text" name="attr' + value.id + '" id="smh-attr' + value.id + '" class="form-control" placeholder="' + value.name + '" />' +
                            '</div>' +
                            '</div>';
                });
            }

            content = '<ul role="tablist" class="nav nav-tabs">' +
                    '<li class="active" role="presentation"><a data-toggle="tab" role="tab" aria-controls="home" href="#login-tab" aria-expanded="true" onclick="ppv_obj.clearRegister();">Login</a></li>' +
                    '<li role="presentation"><a data-toggle="tab" role="tab" aria-controls="profile" href="#register-tab" aria-expanded="false" onclick="ppv_obj.clearLogin();">Register</a></li>' +
                    '</ul>' +
                    '<div class="tab-content">' +
                    '<div id="login-tab" class="tab-pane text-center active" role="tabpanel">' +
                    '<form id="smh-login-form" action="">' +
                    '<div id="login-fail"></div>' +
                    '<div id="login-table">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px; padding-left: 10px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="username" id="smh-username" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-lock"></i></div>' +
                    '<input type="password" name="pass" id="smh-pass" class="form-control" placeholder="Password" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px;" id="signin" onclick="ppv_obj.smhLogin(true); return false;">Login</button>' +
                    '<div style="float: right; margin-right: 15px; margin-top: 7px;"><a href="#" onclick="ppv_obj.smh_register_login_password_form(' + ticket + ',' + entry + ',\'' + type + '\',\'' + bill_per + '\',\'' + entry_id + '\'); return false;">Forgot Password?</a></div>' +
                    '<span style="margin-left: 20px; margin-right: 10px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="login-loading"></span>' +
                    '<div style="float: left;"><button onclick="ppv_obj.smh_back(\'' + entry_id + '\'); return false;" class="btn btn-default" type="button">Back</button></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '<div id="register-tab" class="tab-pane text-center" role="tabpanel">' +
                    '<form id="smh-register-form" action="">' +
                    '<div id="register-fail"></div>' +
                    '<div id="register-table">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-user"></i></div>' +
                    '<input type="text" name="fname" id="smh-fname" class="form-control" placeholder="First Name" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-user"></i></div>' +
                    '<input type="text" name="lname" id="smh-lname" class="form-control" placeholder="Last Name" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="email" id="smh-email" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-lock"></i></div>' +
                    '<input type="password" name="registerPass" id="smh-register-pass" class="form-control" placeholder="Password" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-lock"></i></div>' +
                    '<input type="password" name="registerPass2" id="smh-register-pass2" class="form-control" placeholder="Confirm Password" />' +
                    '</div>' +
                    '</div>' +
                    additional_attr +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" id="register-button" style="float: right; width: 95px;" onclick="ppv_obj.register(true); return false;">Register</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right;" id="register-loading"></span>' +
                    '<div style="float: left;"><button onclick="ppv_obj.smh_back(\'' + entry_id + '\'); return false;" class="btn btn-default" type="button">Back</button></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</div>';
            $smh('#smh_purchase_window .modal-body').html(content);

            $smh('#smh-register-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            register_validator = $smh("#smh-register-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    fname: {
                        required: true
                    },
                    lname: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    registerPass: {
                        required: true,
                        mypassword: true,
                        minlength: 5
                    },
                    registerPass2: {
                        required: true,
                        minlength: 5,
                        equalTo: '#smh-register-pass'
                    }
                },
                messages: {
                    fname: {
                        required: "Please enter a first name"
                    },
                    lname: {
                        required: "Please enter a last name"
                    },
                    email: {
                        required: "Please enter an email",
                        email: "Please enter a valid email"
                    },
                    registerPass: {
                        required: "Please enter a password",
                        minlength: "Your password must be at least 5 characters long"
                    },
                    registerPass2: {
                        required: "Please enter a password",
                        minlength: "Your password must be at least 5 characters long",
                        equalTo: 'Passwords do not match'
                    }
                }
            });

            if (owner_attr) {
                $smh.each(owner_attr, function (index, value) {
                    if ((value.required == '1')) {
                        $smh("#smh-attr" + value.id).rules("add", {
                            required: true,
                            messages: {
                                required: "This field is required"
                            }
                        });
                    }
                });
            }

            $smh('#smh-login-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            login_validator = $smh("#smh-login-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    username: {
                        required: true,
                        email: true
                    },
                    pass: {
                        required: true
                    }
                },
                messages: {
                    username: {
                        required: "Please enter an email",
                        email: "Please enter a valid email"
                    },
                    pass: {
                        required: "Please enter a password"
                    }
                }
            });
        },
        clearLogin: function () {
            $smh('#smh-login-form input').tooltipster('hide');
            $smh('#smh-login-form input').removeClass('validate-error');
        },
        clearRegister: function () {
            $smh('#smh-register-form input').tooltipster('hide');
            $smh('#smh-register-form input').removeClass('validate-error');
        },
        register: function (pr) {
            var timezone = jstz.determine();
            var tz = timezone.name();
            var valid = register_validator.form();
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var currentURL = window.location.href;
            var owner_attr_values = new Array();
            if (valid) {
                var fname = $smh('#smh-fname').val();
                var lname = $smh('#smh-lname').val();
                var email = $smh('#smh-email').val();
                var pass = $smh('#smh-register-pass').val();

                if (owner_attr) {
                    $smh.each(owner_attr, function (index, value) {
                        owner_attr_values.push({
                            field_name: value.name,
                            id: value.id,
                            required: value.required,
                            value: $smh('#smh-attr' + value.id).val()
                        });
                    });
                }

                var sessData = {
                    pid: pid,
                    sm_ak: sm_ak,
                    fname: fname,
                    lname: lname,
                    email: email,
                    pass: pass,
                    tz: tz,
                    url: ppv_obj.base64_encode(currentURL),
                    attrs: (owner_attr_values.length) ? JSON.stringify(owner_attr_values) : null
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=register_account",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#register-button').attr('disabled', '');
                        $smh('#register-loading').html('<img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function (data) {
                    if (data['success']) {
                        if (data['auth_key']) {
                            if (blocked) {
                                window.ppv.endResize();
                            }
                            is_logged_in = true;
                            $smh('#login-loading').empty();
                            $smh.cookie('smh_auth_key', data['auth_key']);
                            window.smh_ppv_order['userId'] = data['user_id'];
                            userId = data['user_id'];
                            ppv_obj.checkUserInventory(pid, sm_ak, data['user_id'], entryId, type, pr);
                            if (!active_interval_set) {
                                window.ppv.isActive(pid, sm_ak);
                                is_active = setInterval(function () {
                                    window.ppv.isActive(pid, sm_ak);
                                }, 600000);
                                active_interval_set = true;
                            }
                        } else {
                            ppv_obj.activateForm(email);
                        }
                    } else {
                        $smh('#register-fail').html('User already exists. Please try again');
                        $smh('#register-fail').css('display', 'block');
                        $smh('#register-loading').empty();
                        setTimeout(function () {
                            $smh('#register-fail').css('display', 'none');
                            $smh('#register-button').removeAttr('disabled');
                        }, 3000);
                    }
                });
            }
        },
        activateForm: function (email) {
            ppv_obj.resetModal();
            var header, content;
            $smh('.modal-dialog').css('width', '400px');

            header = '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Activate</h4>';
            $smh('#smh_purchase_window .modal-header').html(header);

            content = '<div id="pass-instructions">Thank you for registering! A confirmation email has been sent to:<br><br> <strong>' + email + '</strong>.<br><br> Please click on the activation link in the email to activate your account. If you did not receive an email, please check your spam.</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-default" style="float: right;" data-dismiss="modal">Close</button>' +
                    '</div>' +
                    '<div class="clear"></div>';
            $smh('#smh_purchase_window .modal-body').html(content);
        },
        confirm: function () {
            var type = this.getConfig('type');
            var entryId = this.getConfig('entryId');
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entryId: entryId,
                ticket_id: window.smh_ppv_order['ticket'],
                type: type,
                protocol: protocol,
                has_start: (start_date) ? true : false
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_confirm",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#smh_purchase_window').modal({
                        backdrop: 'static'
                    });
                    $smh('.modal-dialog').css('width', '520px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function (msg) {
                ppv_obj.resetModal();
                if (msg['success']) {
                    $smh('.modal-content').html('<div class="modal-header">' +
                            '<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">x</span></button><h4 class="modal-title">Order Summary</h4>' +
                            '<div class="clear"></div>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            msg['content'] +
                            '<div id="register-buttons">' +
                            '<button type="button" class="btn btn-primary" style="float: right;" id="pay" onclick="ppv_obj.pay();">Confirm and Pay</button>' +
                            '<button type="button" class="btn btn-default" style="margin-right: 10px; float: right;" onclick="ppv_obj.smh_back_loggedin(\'' + entryId + '\'); return false;">Back</button>' +
                            '<div class="clear"></div>' +
                            '</div>' +
                            '</div>');
                } else {
                    $smh('.modal-content').html('<div class="modal-header">' +
                            '<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">x</span></button><h4 class="modal-title">Error</h4>' +
                            '<div class="clear"></div>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            '<div style="height: 55px; margin-top: 30px; width: 244px; margin-left: auto; margin-right: auto;">Something went wrong. Please try again later.</div>' +
                            '</div>');
                }
            });
        },
        loggedin_confirm: function (ticket, entry, type, bill_per) {
            window.smh_ppv_order['entry'] = entry;
            window.smh_ppv_order['ticket'] = ticket;
            window.smh_ppv_order['type'] = type;
            window.smh_ppv_order['bill_per'] = bill_per;
            var entryId = this.getConfig('entryId');

            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entryId: entryId,
                ticket_id: window.smh_ppv_order['ticket'],
                type: this.getConfig('type'),
                protocol: protocol,
                has_start: (start_date) ? true : false
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_confirm",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#smh_purchase_window').modal({
                        backdrop: 'static'
                    });
                    $smh('.modal-dialog').css('width', '520px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function (msg) {
                ppv_obj.resetModal();
                if (msg['success']) {
                    $smh('.modal-content').html('<div class="modal-header">' +
                            '<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">x</span></button><h4 class="modal-title">Order Summary</h4>' +
                            '<div class="clear"></div>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            msg['content'] +
                            '<div id="register-buttons">' +
                            '<button type="button" class="btn btn-primary" style="float: right;" onclick="ppv_obj.pay();">Confirm and Pay</button>' +
                            '<button type="button" class="btn btn-default" style="margin-right: 10px; float: right;" onclick="ppv_obj.smh_back_loggedin(\'' + entryId + '\'); return false;">Back</button>' +
                            '<div class="clear"></div>' +
                            '</div>' +
                            '</div>');
                } else {
                    $smh('.modal-content').html('<div class="modal-header">' +
                            '<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">x</span></button><h4 class="modal-title">Error</h4>' +
                            '<div class="clear"></div>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            '<div style="height: 55px; margin-top: 30px; width: 244px; margin-left: auto; margin-right: auto;">Something went wrong. Please try again later.</div>' +
                            '</div>');
                }
            });
        },
        pay: function () {
            var timezone = jstz.determine();
            var tz = timezone.name();
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entry_id: window.smh_ppv_order['entry'],
                user_id: window.smh_ppv_order['userId'],
                ticket_id: window.smh_ppv_order['ticket'],
                tz: tz,
                gw_type: gw_type
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=add_order",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    var isMobile = kWidget.isMobileDevice();
                    var text = '';
                    if (gw_type == 1) {
                        if (isMobile) {
                            text = '<div style="font-size: 18px; font-weight: bold;">Please wait while we redirect you to PayPal</div>';
                        } else {
                            text = '<div style="font-size: 12px; font-weight: bold;">Please wait while your payment is being processed</div>';
                        }
                    }

                    if (gw_type == 2) {
                        if (isMobile) {
                            text = '<div style="font-size: 18px; font-weight: bold;">Please wait while we redirect you to Authorize.net</div>';
                        } else {
                            text = '<div style="font-size: 12px; font-weight: bold;">Please wait while your payment is being processed</div>';
                        }
                    }

                    $smh('#smh_purchase_window').modal({
                        backdrop: 'static'
                    });
                    $smh('.modal-dialog').css('width', '520px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif">' + text + '</div>');
                    if (gw_type == 1) {
                        if (!isMobile) {
                            var width = 800;
                            var height = 600;
                            var left = (screen.width / 2) - (width / 2);
                            var top = (screen.height / 2) - (height / 2);
                            myWindow = window.open("about:blank", "paypal", "location=no,scrollbars=yes,resizable=yes,toolbar=no,menubar=no,width=" + width + ",height=" + height + ",top=" + top + ",left=" + left);
                            myWindow.document.write('<h2>Loading, please wait...</h2>');
                        }
                    } else if (gw_type == 2) {
                        if (!isMobile) {
                            var width = 800;
                            var height = 600;
                            var left = (screen.width / 2) - (width / 2);
                            var top = (screen.height / 2) - (height / 2);
                            myWindow = window.open("about:blank", "authnet", "location=no,scrollbars=yes,resizable=yes,toolbar=no,menubar=no,width=" + width + ",height=" + height + ",top=" + top + ",left=" + left);
                        }
                    }
                }
            }).done(function (data) {
                if (data['success']) {
                    if (gw_type == 1) {
                        ppv_obj.paypal(data['order_id'], data['sub_id']);
                    } else if (gw_type == 2) {
                        ppv_obj.authnet(data);
                    }
                }
            });
        },
        pollOrderStatus: function () {
            order_timer = setInterval(function () {
                ppv_obj.check_order();
            }, 10000);
        },
        check_order: function () {
            var timezone = jstz.determine();
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');
            var type = this.getConfig('type');
            var entryId = this.getConfig('entryId');
            var sessData = {
                entryId: entryId,
                uid: userId,
                pid: pid,
                sm_ak: sm_ak,
                type: type,
                tz: timezone.name()
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=check_inventory",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                if (data) {
                    clearInterval(order_timer);
                    clearInterval(pop_up_timer);
                    $smh('#smh_purchase_window').modal('hide');
                    paid = true;
                    window.ppv.loadVideo(data, pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                }
            });
        },
        checkWindowClosed: function (oid, sid) {
            pop_up_timer = setInterval(function () {
                if (myWindow.closed) {
                    clearInterval(pop_up_timer);
                    ppv_obj.cancel_order(oid, sid);
                }
            }, 1000);
        },
        changeFname: function (fname) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="ppv_obj.smh_back_settings(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<form id="smh-settings-form" action="">' +
                    '<div id="settings-table">' +
                    '<div class="form-group">' +
                    '<h2 style="float: left;">First Name</h2>' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px; padding-left: 10px;"><i class="fa fa-user"></i></div>' +
                    '<input type="text" name="fname" id="smh-fname" class="form-control" placeholder="Enter your first name" value="' + fname + '" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px; float: right;" id="update" onclick="ppv_obj.updateFname(); return false;">Update</button>' +
                    '<div id="result"></div>' +
                    '<span id="btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>');

            $smh('#smh-settings-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            settings_validator = $smh("#smh-settings-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    fname: {
                        required: true
                    }
                },
                messages: {
                    fname: {
                        required: "Please enter a first name"
                    }
                }
            });
        },
        updateFname: function () {
            var valid = settings_validator.form();
            if (valid) {
                var smh_sess = $smh.cookie('smh_auth_key');
                var pid = this.getConfig('pid');
                var sm_ak = this.getConfig('sm_ak');
                var fname = $smh('#smh-settings-form #smh-fname').val();
                var sessData = {
                    fname: fname,
                    pid: pid,
                    sm_ak: sm_ak,
                    auth_key: smh_sess
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=update_fname",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#btn-loading').css('display', 'block');
                        $smh('#update').attr('disabled', '');
                    }
                }).done(function (data) {
                    $smh('#btn-loading').css('display', 'none');
                    if (data['success']) {
                        $smh('#smh_purchase_window #result').html('<span class="label label-success">Successfully Updated!</span>');
                        setTimeout(function () {
                            $smh('#smh_purchase_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    } else {
                        $smh('#smh_purchase_window #result').html('<span class="label label-danger">Error, could not update!</span>');
                        setTimeout(function () {
                            $smh('#smh_purchase_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    }
                });
            }
        },
        changeLname: function (lname) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="ppv_obj.smh_back_settings(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<form id="smh-settings-form" action="">' +
                    '<div id="settings-table">' +
                    '<div class="form-group">' +
                    '<h2 style="float: left;">Last Name</h2>' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px; padding-left: 10px;"><i class="fa fa-user"></i></div>' +
                    '<input type="text" name="lname" id="smh-lname" class="form-control" placeholder="Enter your last name" value="' + lname + '" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px; float: right;" id="update" onclick="ppv_obj.updateLname(); return false;">Update</button>' +
                    '<div id="result"></div>' +
                    '<span id="btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>');

            $smh('#smh-settings-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            settings_validator = $smh("#smh-settings-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    lname: {
                        required: true
                    }
                },
                messages: {
                    lname: {
                        required: "Please enter a last name"
                    }
                }
            });
        },
        updateLname: function () {
            var valid = settings_validator.form();
            if (valid) {
                var smh_sess = $smh.cookie('smh_auth_key');
                var pid = this.getConfig('pid');
                var sm_ak = this.getConfig('sm_ak');
                var lname = $smh('#smh-settings-form #smh-lname').val();
                var sessData = {
                    lname: lname,
                    pid: pid,
                    sm_ak: sm_ak,
                    auth_key: smh_sess
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=update_lname",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#btn-loading').css('display', 'block');
                        $smh('#update').attr('disabled', '');
                    }
                }).done(function (data) {
                    $smh('#btn-loading').css('display', 'none');
                    if (data['success']) {
                        $smh('#smh_purchase_window #result').html('<span class="label label-success">Successfully Updated!</span>');
                        setTimeout(function () {
                            $smh('#smh_purchase_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    } else {
                        $smh('#smh_purchase_window #result').html('<span class="label label-danger">Error, could not update!</span>');
                        setTimeout(function () {
                            $smh('#smh_purchase_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    }
                });
            }
        },
        changeEmail: function (email) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="ppv_obj.smh_back_settings(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<div id="smh-request">' +
                    '<h2 style="font-size: 13px;">To change your email, click on the button below to submit your request.</h2>' +
                    '<button onclick="ppv_obj.updateEmail(\'' + email + '\'); return false;" id="update" style="float: none; margin-left: auto; margin-right: auto;" class="btn btn-primary" type="button">Submit Request</button>' +
                    '<span id="request-btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif"></span>' +
                    '</div>');
        },
        changePsswd: function (email) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="ppv_obj.smh_back_settings(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<div id="smh-request">' +
                    '<h2 style="font-size: 13px;">To change your password, click on the button below to submit your request.</h2>' +
                    '<button onclick="ppv_obj.updatePassword(\'' + email + '\'); return false;" id="update" style="float: none; margin-left: auto; margin-right: auto;" class="btn btn-primary" type="button">Submit Request</button>' +
                    '<span id="request-btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif"></span>' +
                    '</div>');
        },
        updateEmail: function (email) {
            var sm_ak = this.getConfig('sm_ak');
            var currentURL = window.location.href;
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: sm_ak,
                email: email,
                url: ppv_obj.base64_encode(currentURL)
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=reset_email_request",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#request-btn-loading').css('display', 'inline');
                    $smh('#update').attr('disabled', '');
                }
            }).done(function (data) {
                $smh('#request-btn-loading').css('display', 'none');
                $smh('#update').removeAttr('disabled');
                if (data['success']) {
                    $smh('#smh-request').html('<div id="pass-instructions" style="font-size: 13px; margin-top: 30px; margin-bottom: 30px;">An email has been sent to <strong>' + email + '</strong><br> with instructions on how to reset your email.</div>');
                } else {
                    $smh('#smh-request').html('<div id="pass-instructions" style="margin-top: 30px; margin-bottom: 30px;"><h2>An error occurred. Please try again later.</h2></div>');
                }
            });
        },
        updatePassword: function (email) {
            var sm_ak = this.getConfig('sm_ak');
            var currentURL = window.location.href;
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: sm_ak,
                email: email,
                url: ppv_obj.base64_encode(currentURL)
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=reset_psswd_request",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#request-btn-loading').css('display', 'inline');
                    $smh('#update').attr('disabled', '');
                }
            }).done(function (data) {
                $smh('#request-btn-loading').css('display', 'none');
                $smh('#update').removeAttr('disabled');
                if (data['success']) {
                    $smh('#smh-request').html('<div id="pass-instructions" style="font-size: 13px; margin-top: 30px; margin-bottom: 30px;">An email has been sent to <strong>' + email + '</strong><br> with instructions on how to reset your password.</div>');
                } else {
                    $smh('#smh-request').html('<div id="pass-instructions" style="margin-top: 30px; margin-bottom: 30px;"><h2>An error occurred. Please try again later.</h2></div>');
                }
            });
        },
        smh_back_settings: function () {
            var smh_sess = $smh.cookie('smh_auth_key');
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');

            var sessData = {
                uid: userId,
                pid: pid,
                sm_ak: sm_ak,
                auth_key: smh_sess
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_user_details",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                $smh('#settings-tab').html('<div class="profile-row">' +
                        '<span class="profile-option">First Name</span><span class="profile-change" onclick="ppv_obj.changeFname(\'' + data['fname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['fname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Last Name</span><span class="profile-change" onclick="ppv_obj.changeLname(\'' + data['lname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['lname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Email</span><span class="profile-change" onclick="ppv_obj.changeEmail(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['email'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Password</span><span class="profile-change" onclick="ppv_obj.changePsswd(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">*******</div>');
            });
        },
        smhProfile: function () {
            var header, content;
            $smh('#smh_purchase_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '800px');

            ppv_obj.resetModal();
            header = '<button type="button" class="close" onclick="ppv_obj.destroyToolTipSettings();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">User Profile</h4>';
            $smh('#smh_purchase_window .modal-header').html(header);

            var smh_sess = $smh.cookie('smh_auth_key');
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');

            var sessData = {
                uid: userId,
                pid: pid,
                sm_ak: sm_ak,
                auth_key: smh_sess
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_user_details",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {

                content = '<ul role="tablist" class="nav nav-tabs">' +
                        '<li class="active" role="presentation"><a data-toggle="tab" role="tab" href="#settings-tab">Settings</a></li>' +
                        '<li role="presentation"><a data-toggle="tab" role="tab" href="#orders-tab">Orders</a></li>' +
                        '<li role="presentation"><a data-toggle="tab" role="tab" href="#subs-tab">Subscriptions</a></li>' +
                        '</ul>' +
                        '<div class="tab-content">' +
                        '<div id="settings-tab" class="tab-pane text-center active" role="tabpanel">' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">First Name</span><span class="profile-change" onclick="ppv_obj.changeFname(\'' + data['fname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['fname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Last Name</span><span class="profile-change" onclick="ppv_obj.changeLname(\'' + data['lname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['lname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Email</span><span class="profile-change" onclick="ppv_obj.changeEmail(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['email'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Password</span><span class="profile-change" onclick="ppv_obj.changePsswd(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">*******</div>' +
                        '</div>' +
                        '<div id="orders-tab" class="tab-pane text-center" role="tabpanel">' +
                        '<table cellpadding="0" cellspacing="0" border="0" class="display content-data" id="orders-data"></table>' +
                        '</div>' +
                        '<div id="subs-tab" class="tab-pane text-center" role="tabpanel">' +
                        '<table cellpadding="0" cellspacing="0" border="0" class="display content-data" id="subs-data"></table>' +
                        '</div>' +
                        '</div>';
                $smh('#smh_purchase_window .modal-body').html(content);

                user_orders_table = $smh('#orders-data').DataTable({
                    "dom": '<"H"lfr>t<"F"ip>',
                    "order": [],
                    "ordering": false,
                    "jQueryUI": false,
                    "processing": true,
                    "serverSide": true,
                    "autoWidth": false,
                    "pagingType": "simple_numbers",
                    "pageLength": 5,
                    "searching": false,
                    "info": false,
                    "lengthChange": false,
                    "ajax": {
                        "url": protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_get_user_orders",
                        "type": "GET",
                        "data": function (d) {
                            var smh_sess = $smh.cookie('smh_auth_key');
                            return $smh.extend({}, d, {
                                "pid": ppv_obj.getConfig('pid'),
                                "sm_ak": ppv_obj.getConfig('sm_ak'),
                                "uid": userId,
                                "auth_key": smh_sess
                            });
                        }
                    },
                    "language": {
                        "zeroRecords": "No orders found"
                    },
                    "columns": [
                        {
                            "title": "<span style='float: left;'>Order Date</span>",
                            "width": "92px"
                        },
                        {
                            "title": "<span style='float: left;'>Title</span>"
                        },
                        {
                            "title": "<span style='float: left;'>Price</span>",
                            "width": "92px"
                        },
                        {
                            "title": "<span style='float: left;'>Expires</span>",
                            "width": "92px"
                        },
                        {
                            "title": "<span style='float: left;'>Views</span>",
                            "width": "92px"
                        },
                        {
                            "title": "<span style='float: left;'>Status</span>",
                            "width": "92px"
                        },
                    ]
                });
                ppv_obj.load_user_subs();
            });

        },
        delete_sub: function (sid) {
            $smh('#subs-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="ppv_obj.smh_back_subs(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<div id="smh-request">' +
                    '<h2 style="font-size: 13px;">Are you sure you want to cancel this subscription?</h2>' +
                    '<button onclick="ppv_obj.doDeleteSub(' + sid + '); return false;" id="update" style="float: none; margin-left: auto; margin-right: auto;" class="btn btn-primary" type="button">Cancel Subscription</button>' +
                    '<span id="request-btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif"></span>' +
                    '</div>');
        },
        cancel_sub: function (sid) {
            $smh('#subs-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="ppv_obj.smh_back_subs(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<div id="smh-request">' +
                    '<h2 style="font-size: 13px;">Are you sure you want to cancel this subscription?</h2>' +
                    '<button onclick="ppv_obj.doSubCancel(' + sid + '); return false;" id="update" style="float: none; margin-left: auto; margin-right: auto;" class="btn btn-primary" type="button">Cancel Subscription</button>' +
                    '<span id="request-btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif"></span>' +
                    '</div>');
        },
        smh_back_subs: function () {
            $smh('#subs-tab').html('<table cellpadding="0" cellspacing="0" border="0" class="display content-data" id="subs-data"></table>');
            ppv_obj.load_user_subs();
        },
        load_user_subs: function () {
            user_subs_table = $smh('#subs-data').DataTable({
                "dom": '<"H"lfr>t<"F"ip>',
                "order": [],
                "ordering": false,
                "jQueryUI": false,
                "processing": true,
                "serverSide": true,
                "autoWidth": false,
                "pagingType": "simple_numbers",
                "pageLength": 5,
                "searching": false,
                "info": false,
                "lengthChange": false,
                "ajax": {
                    "url": protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_get_user_subs",
                    "type": "GET",
                    "data": function (d) {
                        var smh_sess = $smh.cookie('smh_auth_key');
                        return $smh.extend({}, d, {
                            "pid": ppv_obj.getConfig('pid'),
                            "sm_ak": ppv_obj.getConfig('sm_ak'),
                            "uid": userId,
                            "auth_key": smh_sess
                        });
                    }
                },
                "language": {
                    "zeroRecords": "No subscriptions found"
                },
                "columns": [
                    {
                        "title": "<span style='float: left;'>Date Started</span>",
                        "width": "92px"
                    },
                    {
                        "title": "<span style='float: left;'>Term</span>"
                    },
                    {
                        "title": "<span style='float: left;'>Price</span>"
                    },
                    {
                        "title": "<span style='float: left;'>Status</span>"
                    },
                    {
                        "title": "<span style='float: left;'>Action</span>"
                    },
                ]
            });
        },
        doDeleteSub: function (sid) {
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var sessData = {
                sub_id: sid,
                pid: pid,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_delete_sub",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#request-btn-loading').css('display', 'inline');
                    $smh('#update').attr('disabled', '');
                }
            }).done(function (data) {
                $smh('#request-btn-loading').css('display', 'none');
                $smh('#update').removeAttr('disabled');
                if (data['success']) {
                    $smh('#smh-request').html('<div id="pass-instructions" style="font-size: 13px; margin-top: 30px; margin-bottom: 30px;"><h2 style="font-size: 13px;">This subscription has been canceled.</h2></div>');
                } else {
                    $smh('#smh-request').html('<div id="pass-instructions" style="margin-top: 30px; margin-bottom: 30px;"><h2>An error occurred. Please try again later.</h2></div>');
                }
            });
        },
        doSubCancel: function (sid) {
            var smh_sess = $smh.cookie('smh_auth_key');
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var sessData = {
                sub_id: sid,
                pid: pid,
                sm_ak: sm_ak,
                auth_key: smh_sess
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_cancel_sub",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#request-btn-loading').css('display', 'inline');
                    $smh('#update').attr('disabled', '');
                }
            }).done(function (data) {
                $smh('#request-btn-loading').css('display', 'none');
                $smh('#update').removeAttr('disabled');
                if (data['success']) {
                    $smh('#smh-request').html('<div id="pass-instructions" style="font-size: 13px; margin-top: 30px; margin-bottom: 30px;"><h2 style="font-size: 13px;">This subscription has been canceled.</h2></div>');
                } else {
                    $smh('#smh-request').html('<div id="pass-instructions" style="margin-top: 30px; margin-bottom: 30px;"><h2>An error occurred. Please try again later.</h2></div>');
                }
            });
        },
        cancel_order: function (oid, sid) {
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                uid: window.smh_ppv_order['userId'],
                order_id: oid,
                sub_id: sid
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=cancel_order",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                if (data['success']) {
                    clearInterval(order_timer);
                    if (blocked) {
                        $smh('#smh_purchase_window').modal('hide');
                    } else {
                        $smh('#smh_purchase_window').modal('hide');

                    }
                }
            });
        },
        authnet: function (data) {
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var ticket_type = window.smh_ppv_order['type'];
            var title = '';
            var isMobile = kWidget.isMobileDevice();
            if (data['sub_id'] == -1) {
                title = data['options']['title'];
            } else {
                var period = window.smh_ppv_order['bill_per'];
                if (period == 'w') {
                    title = data['options']['title'] + ' - Weekly Subscription';
                }
                if (period == 'm') {
                    title = data['options']['title'] + ' - Monthly Subscription';
                }
                if (period == 'y') {
                    title = data['options']['title'] + ' - Yearly Subscription';
                }
            }

            var target = (isMobile) ? '_self' : 'authnet';
            var url = window.location.href;
            var form = $smh('<form action="https://secure2.authorize.net/gateway/transact.dll" method="post" target="' + target + '">' +
                    '<input type="hidden" name="x_fp_sequence" value="' + data['options']['sequence'] + '">' +
                    '<input type="hidden" name="x_fp_timestamp" value="' + data['options']['tstamp'] + '">' +
                    '<input type="hidden" name="x_fp_hash" value="' + data['options']['fp'] + '">' +
                    '<input type="hidden" name="x_description" value="' + title + '">' +
                    '<input type="hidden" name="x_login" value="' + data['options']['login'] + '">' +
                    '<input type="hidden" name="x_amount" value="' + data['options']['amount'] + '">' +
                    '<input type="hidden" name="x_currency_code" value="' + data['options']['currency'] + '">' +
                    '<input type="hidden" name="x_first_name" value="' + data['options']['fname'] + '">' +
                    '<input type="hidden" name="x_last_name" value="' + data['options']['lname'] + '">' +
                    '<input type="hidden" name="x_email" value="' + data['options']['email'] + '">' +
                    '<input type="hidden" name="x_email_customer" value="FALSE">' +
                    '<input type="hidden" name="x_cancel_url" value="https://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/authnet_cancel.php?oid=' + data['order_id'] + '&sid=' + data['sub_id'] + '&pid=' + pid + '&sm_ak=' + encodeURIComponent(sm_ak) + '">' +
                    '<input type="hidden" name="x_cancel_url_text" value="Cancel_Order">' +
                    '<input type="hidden" name="x_rename" value="x_description, Title">' +
                    '<input type="hidden" name="x_version" value="3.1">' +
                    '<input type="hidden" name="custom" value="' + data['order_id'] + ',' + encodeURIComponent(sm_ak) + ',' + ticket_type + ',' + data['sub_id'] + ',' + encodeURIComponent(smh_aff) + ',' + isMobile + '">' +
                    '<input type="hidden" name="url" value="' + url + '">' +
                    '<input type="hidden" name="x_invoice_num" value="' + pid + 'X' + data['order_id'] + '">' +
                    '<input type="hidden" name="x_show_form" value="PAYMENT_FORM">' +
                    '<input type="hidden" name="x_test_request" value="FALSE">' +
                    '<input type="hidden" name="x_relay_response" value="TRUE">' +
                    '<input type="hidden" name="x_relay_always" value="TRUE">' +
                    '<input type="hidden" name="x_relay_url" value="https://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/authnet-relay.php">' +
                    '</form>');
            $smh('body').append(form);
            $smh(form).submit();
            if (!isMobile) {
                ppv_obj.checkWindowClosed(data['order_id'], data['sub_id']);
                ppv_obj.pollOrderStatus();
            }
        },
        paypal: function (order_id, sub_id) {
            var timezone = jstz.determine();
            var userId = window.smh_ppv_order['userId'];
            var entryId = window.smh_ppv_order['entry'];
            var ticketId = window.smh_ppv_order['ticket'];
            var ticket_type = window.smh_ppv_order['type'];
            var bill_per = window.smh_ppv_order['bill_per'];
            var kentry = ppv_obj.getConfig('entryId');
            var sm_ak = ppv_obj.getConfig('sm_ak');
            var isMobile = kWidget.isMobileDevice();
            var currentURL = window.location.href;
            var type = ppv_obj.getConfig('type');

            var sessData = {
                method: 'getToken',
                entryId: entryId,
                qty: '1',
                userId: userId,
                mobile: isMobile,
                type: type,
                ticketId: ticketId,
                kentry: kentry,
                orderId: order_id,
                sm_ak: sm_ak,
                smh_aff: smh_aff,
                ticket_type: ticket_type,
                bill_per: bill_per,
                subId: sub_id,
                protocol: protocol,
                url: currentURL,
                tz: timezone.name()
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/php/pptransact.php",
                data: sessData,
                dataType: 'json'
            }).done(function (resp) {
                if (resp.success) {
                    if (!isMobile) {
                        myWindow.location = 'https://www.paypal.com/checkoutnow?token=' + resp.token;
                        ppv_obj.checkWindowClosed(order_id, sub_id);
                        ppv_obj.pollOrderStatus();
                    } else {
                        ppv_obj.redirect('https://www.paypal.com/checkoutnow?token=' + resp.token);
                    }
                } else {
                    myWindow.close();
                    $smh('.modal-content').html('<div class="modal-header">' +
                            '<button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">x</span></button><h4 class="modal-title">Error</h4>' +
                            '<div class="clear"></div>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            '<div style="height: 55px; margin-top: 30px; width: 344px; margin-left: auto; margin-right: auto;">Something went wrong. Please contact the website administrator.</div>' +
                            '</div>');
                    setTimeout(function () {
                        $smh('#smh_purchase_window').modal('hide');
                    }, 5000);
                }
            });
        },
        redirect: function (url) {
            var ua = navigator.userAgent.toLowerCase(),
                    isIE = ua.indexOf('msie') !== -1,
                    version = parseInt(ua.substr(4, 2), 10);

            // Internet Explorer 8 and lower
            if (isIE && version < 9) {
                var link = document.createElement('a');
                link.href = url;
                document.body.appendChild(link);
                link.click();
            }

            // All other browsers
            else {
                window.location.href = url;
            }
        },
        base64_encode: function (data) {
            var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
            var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
                    ac = 0,
                    enc = '',
                    tmp_arr = [];

            if (!data) {
                return data;
            }

            do { // pack three octets into four hexets
                o1 = data.charCodeAt(i++);
                o2 = data.charCodeAt(i++);
                o3 = data.charCodeAt(i++);

                bits = o1 << 16 | o2 << 8 | o3;

                h1 = bits >> 18 & 0x3f;
                h2 = bits >> 12 & 0x3f;
                h3 = bits >> 6 & 0x3f;
                h4 = bits & 0x3f;

                // use hexets to index into b64, and append result to encoded string
                tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
            } while (i < data.length);

            enc = tmp_arr.join('');

            var r = data.length % 3;

            return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
        },
        showLoggedInPurchaseWindow: function () {
            kdp.sendNotification('doPause');
            var sessData;
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');

            sessData = {
                pid: pid,
                sm_ak: sm_ak,
                entryId: entryId,
                type: type,
                protocol: protocol,
                logged_in: is_logged_in,
                has_start: (start_date) ? true : false
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#smh_purchase_window').modal({
                        backdrop: 'static'
                    });
                    $smh('.modal-dialog').css('width', '400px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function (msg) {
                ppv_obj.resetModal();
                if (!msg['success']) {
                    $smh('.modal-dialog').css('width', '600px');
                    $smh('.modal-content').html(msg['content']);
                } else {
                    if (msg['content']['success']) {
                        window.smh_ppv_order['userId'] = userId;
                        is_logged_in = true;
                        $smh('.modal-dialog').css('width', '400px');
                        var header, content;
                        $smh('#smh_purchase_window .modal-content').html('<div class="modal-header"></div><div class="modal-body"></div>');
                        header = '<button type="button" class="close purchase-close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Tickets</h4>';
                        $smh('#smh_purchase_window .modal-header').html(header);

                        var entry_desc = '';
                        if (msg['content']['desc']) {
                            entry_desc = '<div id="entry-desc">' + msg['content']['desc'] + '</div>';
                        }
                        content = entry_desc +
                                '<div id="ticket-wrapper">' + msg['content']['tickets'] + '</div>' +
                                '<div class="clear"></div>';

                        $smh('#smh_purchase_window .modal-body').html(content);
                    } else {
                        $smh('.modal-dialog').css('width', '600px');
                        $smh('.modal-content').html(msg['content']['content']);
                    }
                }
            });
        },
        login_form: function (entryId) {
            var header, content;
            $smh('#smh_purchase_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '398px');

            header = '<span style="font-size: 15px;">Login</span><button type="button" class="close" onclick="ppv_obj.destroyToolTipLogin();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button>';
            $smh('#smh_purchase_window .modal-header').html(header);

            content = '<div id="form-wrapper">' +
                    '<form id="smh-login-form" action="">' +
                    '<div id="login-fail"></div>' +
                    '<div id="login-table" style="margin-top: 10px;">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px; padding-left: 10px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="username" id="smh-username" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-lock"></i></div>' +
                    '<input type="password" name="pass" id="smh-pass" class="form-control" placeholder="Password" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons" style="margin-top: 25px;">' +
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px;" id="signin" onclick="ppv_obj.smhLogin(false); return false;">Login</button>' +
                    '<div style="float: right; margin-right: 15px; margin-top: 7px;"><a href="#" onclick="ppv_obj.smh_login_password_form(\'' + entryId + '\'); return false;">Forgot Password?</a></div>' +
                    '<span style="margin-left: 20px; margin-right: 10px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="login-loading"></span>' +
                    '<div style="float: left;"><button onclick="ppv_obj.smh_back(\'' + entryId + '\'); return false;" class="btn btn-default" type="button">Back</button></div>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>' +
                    '</div>';
            $smh('#smh_purchase_window .modal-body').html(content);

            $smh('#smh-login-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            login_validator = $smh("#smh-login-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    username: {
                        required: true,
                        email: true
                    },
                    pass: {
                        required: true
                    }
                },
                messages: {
                    username: {
                        required: "Please enter an email",
                        email: "Please enter a valid email"
                    },
                    pass: {
                        required: "Please enter a password"
                    }
                }
            });
        },
        resetModal: function () {
            $smh('#smh_purchase_window .modal-content').html('<div class="modal-header"></div><div class="modal-body"></div>');
        },
        login_form_button: function (entryId) {
            ppv_obj.resetModal();
            var header, content;
            $smh('#smh_purchase_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '398px');

            header = '<span style="font-size: 15px;">Login</span><button type="button" class="close" onclick="ppv_obj.destroyToolTipLogin();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button>';
            $smh('#smh_purchase_window .modal-header').html(header);

            content = '<div id="form-wrapper">' +
                    '<form id="smh-login-form" action="">' +
                    '<div id="login-fail"></div>' +
                    '<div id="login-table" style="margin-top: 10px;">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px; padding-left: 10px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="username" id="smh-username" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon"><i class="fa fa-lock"></i></div>' +
                    '<input type="password" name="pass" id="smh-pass" class="form-control" placeholder="Password" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons" style="margin-top: 25px;">' +
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px;" id="signin" onclick="ppv_obj.smhLogin(false); return false;">Login</button>' +
                    '<div style="float: right; margin-right: 15px; margin-top: 7px;"><a href="#" onclick="ppv_obj.smh_password_form_button(\'' + entryId + '\'); return false;">Forgot Password?</a></div>' +
                    '<span style="margin-left: 20px; margin-right: 10px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="login-loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>' +
                    '</div>';
            $smh('#smh_purchase_window .modal-body').html(content);

            $smh('#smh-login-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            login_validator = $smh("#smh-login-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    username: {
                        required: true,
                        email: true
                    },
                    pass: {
                        required: true
                    }
                },
                messages: {
                    username: {
                        required: "Please enter an email",
                        email: "Please enter a valid email"
                    },
                    pass: {
                        required: "Please enter a password"
                    }
                }
            });
        },
        smhLogin: function (pr) {
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var valid = login_validator.form();
            if (valid) {
                var username = $smh('#smh-username').val();
                var password = $smh('#smh-pass').val();
                var sessData = {
                    pid: this.getConfig('pid'),
                    sm_ak: sm_ak,
                    un: username,
                    pswd: password,
                    entryId: entryId
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=login_user",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#login-loading').html('<img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function (data) {
                    if (data['success']) {
                        if (blocked) {
                            window.ppv.endResize();
                        }
                        is_logged_in = true;
                        $smh('#login-loading').empty();
                        $smh.cookie('smh_auth_key', data['auth_key']);
                        window.smh_ppv_order['userId'] = data['user_id'];
                        userId = data['user_id'];
                        ppv_obj.checkUserInventory(pid, sm_ak, data['user_id'], entryId, type, pr);
                        if (!active_interval_set) {
                            window.ppv.isActive(pid, sm_ak);
                            is_active = setInterval(function () {
                                window.ppv.isActive(pid, sm_ak);
                            }, 600000);
                            active_interval_set = true;
                        }
                    } else if (!data['success']) {
                        if (data['au']) {
                            var content = '<h2>Multiple Logins Detected</h2>' +
                                    '<div id="multi-login">You are logged in from at least one other location.<br /> Please log out of other location(s) to access this video.</div>';
                            $smh('#smh_purchase_window .modal-body').html(content);
                        } else {
                            if (data['blocked']) {
                                var content = '<h2>We apologize...</h2>' +
                                        '<div id="multi-login">This video is currently not available. Please try again later.</div>';
                                $smh('#smh_purchase_window .modal-body').html(content);
                            } else {
                                $smh('#login-fail').html('Invalid email or password');
                                $smh('#login-fail').css('display', 'block');
                                $smh('#login-loading').empty();
                                setTimeout(function () {
                                    $smh('#login-fail').css('display', 'none');
                                }, 3000);
                            }
                        }
                    }
                });
            }
        },
        smhLogout: function (entryId) {
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');
            var type = this.getConfig('type');
            $smh.removeCookie('smh_auth_key');
            $smh('#smh_purchase_window').modal('hide');
            is_logged_in = false;
            paid = false;
            clearTimeout(fadeout_timer_m);
            clearTimeout(fadeout_timer);
            $smh("#purchaseWindow").stop(true, true).fadeOut();
            $smh('#purchaseWindow').stop(true, false).fadeIn();
            $smh(document).unbind('mousemove');
            $smh(document).unbind('mouseleave');
            $smh(document).unbind('fadeIn');
            $smh(document).unbind('fadeOut');
            $smh(window).unbind('touchmove');
            window.ppv.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
            window.ppv.isNotActive(pid, sm_ak);
        },
        smh_login_password_form: function (entryId) {
            var header, content;
            $smh('.modal-dialog').css('width', '370px');

            header = '<button type="button" class="close" onclick="ppv_obj.destroyToolTipPass();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Password Recovery</h4>';
            $smh('#smh_purchase_window .modal-header').html(header);

            content = '<div style="font-size: 12px; text-align: left; height: 30px; color: #999;">You can use this form to recover your password if you have forgotten it. Please enter the email address used to register below to get started.</div>' +
                    '<form id="smh-password-form" action="">' +
                    '<div id="register-table">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="email" id="smh-email" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" style="float: right; width: 95px;" id="pass-submit" onclick="ppv_obj.pass_reset_form(\'' + entryId + '\'); return false;">Submit</button>' +
                    '<button type="button" class="btn btn-default" style="float: right; margin-right: 10px;" onclick="ppv_obj.login_form(\'' + entryId + '\'); return false;">Back</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>';

            $smh('#smh_purchase_window .modal-body').html(content);

            $smh('#smh-password-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            pass_validator = $smh("#smh-password-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    email: {
                        required: true,
                        email: true
                    }
                },
                messages: {
                    email: {
                        required: "Please enter an email",
                        email: "Please enter a valid email"
                    }
                }
            });
        },
        smh_register_login_password_form: function (ticket, entry, type, bill_per, entry_id) {
            var header, content;
            $smh('.modal-dialog').css('width', '370px');

            header = '<button type="button" class="close" onclick="ppv_obj.destroyToolTipPass();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Password Recovery</h4>';
            $smh('#smh_purchase_window .modal-header').html(header);

            content = '<div style="font-size: 12px; text-align: left; height: 30px; color: #999;">You can use this form to recover your password if you have forgotten it. Please enter the email address used to register below to get started.</div>' +
                    '<form id="smh-password-form" action="">' +
                    '<div id="register-table">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="email" id="smh-email" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" style="float: right; width: 95px;" id="pass-submit" onclick="ppv_obj.pass_reset_form(); return false;">Submit</button>' +
                    '<button type="button" class="btn btn-default" style="float: right; margin-right: 10px;" onclick="ppv_obj.register_loggin(' + ticket + ',' + entry + ',\'' + type + '\',\'' + bill_per + '\',\'' + entry_id + '\'); return false;">Back</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>';
            $smh('#smh_purchase_window .modal-body').html(content);

            $smh('#smh-password-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            pass_validator = $smh("#smh-password-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    email: {
                        required: true,
                        email: true
                    }
                },
                messages: {
                    email: {
                        required: "Please enter an email",
                        email: "Please enter a valid email"
                    }
                }
            });
        },
        smh_password_form_button: function (entryId) {
            var header, content;
            $smh('.modal-dialog').css('width', '370px');

            header = '<button type="button" class="close" onclick="ppv_obj.destroyToolTipPass();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Password Recovery</h4>';
            $smh('#smh_purchase_window .modal-header').html(header);

            content = '<div style="font-size: 12px; text-align: left; height: 30px; color: #999;">You can use this form to recover your password if you have forgotten it. Please enter the email address used to register below to get started.</div>' +
                    '<form id="smh-password-form" action="">' +
                    '<div id="register-table">' +
                    '<div class="form-group">' +
                    '<div class="input-group">' +
                    '<div class="input-group-addon" style="padding-right: 9px;"><i class="fa fa-envelope"></i></div>' +
                    '<input type="text" name="email" id="smh-email" class="form-control" placeholder="Email" />' +
                    '</div>' +
                    '</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" style="float: right; width: 95px;" id="pass-submit" onclick="ppv_obj.pass_reset_form(); return false;">Submit</button>' +
                    '<button type="button" class="btn btn-default" style="float: right; margin-right: 10px;" onclick="ppv_obj.login_form_button(\'' + entryId + '\'); return false;">Back</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>';

            $smh('#smh_purchase_window .modal-body').html(content);

            $smh('#smh-password-form input').tooltipster({
                trigger: 'custom',
                onlyOne: false,
                position: 'right'
            });

            pass_validator = $smh("#smh-password-form").validate({
                highlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("error").addClass("validate-error");
                },
                unhighlight: function (element, errorClass) {
                    $smh(element).removeClass("valid").removeClass("validate-error");
                },
                errorPlacement: function (error, element) {
                    $smh(element).tooltipster('update', $smh(error).text());
                    $smh(element).tooltipster('show');
                },
                success: function (label, element) {
                    $smh(element).tooltipster('hide');
                },
                rules: {
                    email: {
                        required: true,
                        email: true
                    }
                },
                messages: {
                    email: {
                        required: "Please enter your email",
                        email: "Please enter a valid email"
                    }
                }
            });
        },
        pass_reset_form: function () {
            var valid = pass_validator.form();
            if (valid) {
                var email = $smh('#smh-email').val();
                var sm_ak = this.getConfig('sm_ak');
                var currentURL = window.location.href;
                var sessData = {
                    pid: this.getConfig('pid'),
                    sm_ak: sm_ak,
                    email: email,
                    url: ppv_obj.base64_encode(currentURL)
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=reset_psswd_request",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#pass-submit').attr('disabled', '');
                        $smh('#loading').html('<img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading.gif">');
                    }
                }).done(function (data) {
                    var header, content;
                    if (data['success']) {
                        $smh('.modal-dialog').css('width', '400px');
                        header = '<button type="button" class="close" onclick="" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Password Recovery</h4>';
                        $smh('#smh_purchase_window .modal-header').html(header);

                        content = '<div id="pass-instructions">An email has been sent to <strong>' + email + '</strong><br> with instructions on how to reset your password.</div>' +
                                '</div>' +
                                '<div id="register-buttons">' +
                                '<button type="button" class="btn btn-default" data-dismiss="modal" onclick="" style="float: right;">Close</button>' +
                                '</div>' +
                                '<div class="clear"></div>';
                        $smh('#smh_purchase_window .modal-body').html(content);
                    }
                });
            }
        },
        checkUserInventory: function (pid, sm_ak, uid, entryId, type, pr) {
            var timezone = jstz.determine();
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');

            var sessData = {
                entryId: entryId,
                uid: uid,
                pid: pid,
                sm_ak: sm_ak,
                type: type,
                tz: timezone.name()
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=check_inventory",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                if (!data) {
                    if (pr) {
                        ppv_obj.confirm();
                        window.ppv.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                    } else {
                        var sessData = {
                            pid: pid,
                            sm_ak: sm_ak,
                            entryId: entryId,
                            type: type,
                            protocol: protocol,
                            logged_in: is_logged_in,
                            has_start: (start_date) ? true : false
                        }
                        $smh.ajax({
                            type: "GET",
                            url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                            data: sessData,
                            dataType: 'json',
                            beforeSend: function () {
                                $smh('#smh_purchase_window').modal({
                                    backdrop: 'static'
                                });
                                $smh('.modal-dialog').css('width', '400px');
                                $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                            }
                        }).done(function (msg) {
                            window.ppv.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                            if (!msg['success']) {
                                $smh('.modal-dialog').css('width', '600px');
                                $smh('.modal-content').html(msg['content']);
                            } else {
                                if (msg['content']['success']) {
                                    $smh('.modal-dialog').css('width', '400px');
                                    var header, content;
                                    $smh('#smh_purchase_window .modal-content').html('<div class="modal-header"></div><div class="modal-body"></div>');
                                    header = '<button type="button" class="close purchase-close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Tickets</h4>';
                                    $smh('#smh_purchase_window .modal-header').html(header);

                                    var entry_desc = '';
                                    if (msg['content']['desc']) {
                                        entry_desc = '<div id="entry-desc">' + msg['content']['desc'] + '</div>';
                                    }
                                    content = entry_desc +
                                            '<div id="ticket-wrapper">' + msg['content']['tickets'] + '</div>' +
                                            '<div class="clear"></div>';

                                    $smh('#smh_purchase_window .modal-body').html(content);
                                    userId = uid;
                                } else {
                                    $smh('.modal-dialog').css('width', '600px');
                                    $smh('.modal-content').html(msg['content']['content']);
                                }
                            }
                        });
                    }
                } else {
                    window.ppv.fadeLogout(playerId, entryId);
                    paid = true;
                    is_logged_in = true;
                    userId = uid;
                    $smh('.modal-dialog').css('width', '520px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                    $smh('#purchaseWindow').css('display', 'none');
                    if (!scheduled_is_before && !scheduled_is_after) {
                        kdp.addJsListener("playerPlayed", "playerPlayedHandler");
                        kdp.addJsListener("playerPaused", "playerPausedHandler");

                        if (!playlist && !category) {
                            kdp.setKDPAttribute('servicesProxy.kalturaClient', 'ks', data);
                            kdp.sendNotification('cleanMedia');
                            kdp.sendNotification('changeMedia', {
                                'entryId': entryId
                            });
                        }

                        setTimeout(function () {
                            if (!playlist && !category) {
                                kdp.sendNotification('doPlay');
                            } else if (playlist || category) {
                                init_loaded = false;
                                window.ppv.loadVideo(data, pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                            }

                            $smh('#smh_purchase_window').modal('hide');
                        }, 5000);
                    } else {
                        window.ppv.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                        setTimeout(function () {
                            init_loaded = false;
                            $smh('#smh_purchase_window').modal('hide');
                        }, 5000);
                    }
                }
            });
        },
        smh_back: function (entryId) {
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                type: this.getConfig('type'),
                entryId: entryId,
                protocol: protocol,
                logged_in: is_logged_in,
                has_start: (start_date) ? true : false
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#smh_purchase_window').modal({
                        backdrop: 'static'
                    });
                    $smh('.modal-dialog').css('width', '400px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function (msg) {
                if (!msg['success']) {
                    $smh('.modal-content').html(msg['content']);
                } else {
                    if (msg['content']['success']) {
                        var header, content;
                        $smh('#smh_purchase_window .modal-content').html('<div class="modal-header"></div><div class="modal-body"></div>');
                        header = '<button type="button" class="close purchase-close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Tickets</h4>';
                        $smh('#smh_purchase_window .modal-header').html(header);

                        var entry_desc = '';
                        if (msg['content']['desc']) {
                            entry_desc = '<div id="entry-desc">' + msg['content']['desc'] + '</div>';
                        }
                        content = entry_desc +
                                '<div id="ticket-wrapper">' + msg['content']['tickets'] + '</div>' +
                                '<div class="clear"></div>';

                        $smh('#smh_purchase_window .modal-body').html(content);
                    } else {
                        $smh('.modal-content').html(msg['content']['content']);
                    }
                }
            });
        },
        smh_back_loggedin: function (entryId) {
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var sessData = {
                pid: pid,
                sm_ak: sm_ak,
                type: ppv_obj.getConfig('type'),
                entryId: entryId,
                protocol: protocol,
                logged_in: is_logged_in,
                has_start: (start_date) ? true : false
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=get_tickets",
                data: sessData,
                dataType: 'json',
                beforeSend: function () {
                    $smh('#smh_purchase_window').modal({
                        backdrop: 'static'
                    });
                    $smh('.modal-dialog').css('width', '400px');
                    $smh('.modal-content').html('<div id="ppv-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/ppv/resources/img/loading_icon.gif"></div>');
                }
            }).done(function (msg) {
                ppv_obj.resetModal();
                if (!msg['success']) {
                    $smh('.modal-dialog').css('width', '600px');
                    $smh('.modal-content').html(msg['content']);
                } else {
                    if (msg['content']['success']) {
                        $smh('.modal-dialog').css('width', '400px');
                        var header, content;
                        header = '<button type="button" class="close purchase-close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Tickets</h4>';
                        $smh('#smh_purchase_window .modal-header').html(header);

                        var entry_desc = '';
                        if (msg['content']['desc']) {
                            entry_desc = '<div id="entry-desc">' + msg['content']['desc'] + '</div>';
                        }
                        content = entry_desc +
                                '<div id="ticket-wrapper">' + msg['content']['tickets'] + '</div>' +
                                '<div class="clear"></div>';

                        $smh('#smh_purchase_window .modal-body').html(content);
                    } else {
                        $smh('.modal-dialog').css('width', '600px');
                        $smh('.modal-content').html(msg['content']['content']);
                    }
                }
            });
        },
        updateView: function () {
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: this.getConfig('sm_ak'),
                entryId: this.getConfig('entryId'),
                uid: userId
            }

            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=update_user_views",
                data: sessData,
                dataType: 'json'
            });
        },
        getClipListTarget: function () {
            // check for generated id:
            if ($smh('#' + genClipListId).length) {
                return  $smh('#' + genClipListId);
            }
            var clipListId = ppv_obj.getConfig('clipListTargetId');
            if (clipListId == "null") {
                clipListId = null;
            }
            // check for clip target:
            if (clipListId && $smh('#' + clipListId).length) {
                return  $smh('#' + clipListId)
            }
            // Generate a new clip target ( if none was found )           
            var layout = ppv_obj.getConfig('layoutMode');

            if (layout == 'top') {
                return $smh('<div />').attr('id', genClipListId).insertBefore($smh('#' + playerId));
            } else {
                return $smh('<div />').attr('id', genClipListId).insertAfter($smh('#' + playerId));
            }
        },
        activateEntry: function (activeEntryId) {
            var $carousel = ppv_obj.getClipListTarget().find('.k-carousel');
            // highlight the active clip ( make sure only one clip is highlighted )
            var $clipList = ppv_obj.getClipListTarget().find('ul li');
            ;
            if ($clipList.length && activeEntryId) {
                $clipList.each(function (inx, clipLi) {
                    // kdp moves entryId to .entryId in playlist data provider ( not a db mapping )
                    var entryMeta = $smh(clipLi).data('entryMeta');
                    var clipEntryId = entryMeta;
                    if (clipEntryId == activeEntryId) {
                        $smh(clipLi).addClass('k-active').data('activeEntry', true);

                        // scroll to the target entry ( if not already shown ):
                        if (inx == 0 || ppv_obj.getClipListTarget().find('ul').width() > ppv_obj.getClipListTarget().width()) {
                            $carousel[0].jCarouselLiteGo(inx);
                        }
                    } else {
                        $smh(clipLi).removeClass('k-active').data('activeEntry', false)
                    }
                });
            }
        },
        loadCat: function () {
            kdp.kBind("changeMedia.onPagePlaylist", function (clip) {
                ppv_obj.activateEntry(clip['entryId']);
            });

            kdp.kBind("mediaReady", function () {
                var pid = ppv_obj.getConfig('pid');
                var sm_ak = ppv_obj.getConfig('sm_ak');

                if (addOnce) {
                    return;
                }
                var clipListId = ppv_obj.getConfig('clipListTargetId');
                if (clipListId == "null") {
                    clipListId = null;
                }
                addOnce = true;

                // check for a target
                $clipListTarget = ppv_obj.getClipListTarget();
                // Add a base style class:
                $clipListTarget.addClass('kWidget-clip-list');

                // add layout mode:
                var layoutMode = ppv_obj.getConfig('layoutMode') || 'right';
                $clipListTarget.addClass('k-' + layoutMode);

                // get the thumbWidth:
                var thumbWidth = ppv_obj.getConfig('thumbWidth') || '110';
                // standard 3x4 box ratio:
                var thumbHeight = thumbWidth * .75;

                // calculate how many clips should be visible per size and cliplist Width
                var clipsVisible = null;
                var liSize = {};

                // check layout mode:
                var isLeft = (layoutMode == 'left');
                var isRight = (layoutMode == 'right');
                var isBottom = (layoutMode == 'bottom');
                var isTop = (layoutMode == 'top');
                if (isRight) {
                    // Give player height if dynamically added:
                    if (!clipListId) {
                        // if adding in after the player make sure the player is float left so
                        // the playlist shows up after:
                        $smh(kdp).css('float', 'left');
                        $clipListTarget
                                .css({
                                    'float': 'left',
                                    'padding-left': '5px',
                                    'height': $smh(kdp).height() + 'px',
                                    'width': $smh(kdp).width() + 'px'
                                });
                    }

                    clipsVisible = Math.floor($clipListTarget.height() / (parseInt(thumbHeight) + 4));
                    liSize = {
                        'width': '100%',
                        'height': thumbHeight
                    };
                } else if (isLeft) {
                    // Give player height if dynamically added:
                    if (!clipListId) {
                        // if adding in after the player make sure the player is float left so
                        // the playlist shows up after:
                        $smh(kdp).css('float', 'right');
                        $clipListTarget
                                .css({
                                    'float': 'right',
                                    'padding-right': '5px',
                                    'height': $smh(kdp).height() + 'px',
                                    'width': $smh(kdp).width() + 'px'
                                });
                    }

                    clipsVisible = Math.floor($clipListTarget.height() / (parseInt(thumbHeight) + 4));
                    liSize = {
                        'width': '100%',
                        'height': thumbHeight
                    };
                } else if (isTop) {
                    // horizontal layout
                    // Give it player width if dynamically added:
                    if (!clipListId) {
                        $clipListTarget.css({
                            'margin-bottom': '5px',
                            'width': $smh(kdp).width() + 'px',
                            'height': thumbHeight
                        });
                    }
                    clipsVisible = Math.floor($clipListTarget.width() / (parseInt(thumbWidth) + 4));
                    liSize = {
                        'width': thumbWidth,
                        'height': thumbHeight
                    };
                } else if (isBottom) {
                    // horizontal layout
                    // Give it player width if dynamically added:
                    if (!clipListId) {
                        $clipListTarget.css({
                            'padding-top': '5px',
                            'width': $smh(kdp).width() + 'px',
                            'height': thumbHeight
                        });
                    }
                    clipsVisible = Math.floor($clipListTarget.width() / (parseInt(thumbWidth) + 4));
                    liSize = {
                        'width': thumbWidth,
                        'height': thumbHeight
                    };
                }

                var $clipsUl = $smh('<ul>').css({
                    "height": '100%'
                })
                        .appendTo($clipListTarget)
                        .wrap(
                                $smh('<div />').addClass('k-carousel')
                                )

                // append all the clips
                init_clip = false;
                var first_clip = '';
                $smh.each(cat_entries, function (inx, clip) {

                    if (!init_clip) {
                        first_clip = clip['entry_id'];
                        init_clip = true;
                    }

                    $clipsUl.append(
                            $smh('<li />')
                            .css(liSize)
                            .data({
                                'entryMeta': clip['entry_id'],
                                'index': inx
                            })
                            .append(
                                    $smh('<img />')
                                    .attr({
                                        'src': protocol + '://mediaplatform.streamingmediahosting.com/p/' + pid + '/thumbnail/entry_id/' + clip['entry_id'] + '/width/' + thumbWidth
                                    }),
                                    $smh('<div />')
                                    .addClass('k-clip-desc')
                                    .append(
                                            $smh('<h3 />')
                                            .addClass('k-title')
                                            .text(clip['name']),
                                            $smh('<p />')
                                            .addClass('k-description')
                                            .text((clip['desc'] == null) ? '' : clip['desc'])
                                            )
                                    )
                            .click(function () {
                                ppv_obj.checkCatInvetory(pid, sm_ak, userId, clip['entry_id']);
                            }).hover(function () {
                        $smh(this).addClass('k-active');
                    },
                            function () {
                                // only remove if not the active entry:
                                if (!$smh(this).data('activeEntry')) {
                                    $smh(this).removeClass('k-active');
                                }
                            })
                            )
                });

                // Add scroll buttons
                $clipListTarget.prepend(
                        $smh('<a />')
                        .addClass("k-scroll k-prev")
                        )
                $clipListTarget.append(
                        $smh('<a />')
                        .addClass("k-scroll k-next")
                        )
                // don't show more clips then we have available 
                if (clipsVisible > cat_entries.length) {
                    clipsVisible = cat_entries.length;
                }

                // Add scrolling carousel to clip list ( once dom sizes are up-to-date )
                var verical = false;

                if (isLeft || isRight) {
                    verical = true;
                }

                $clipListTarget.find('.k-carousel').jCarouselLite({
                    btnNext: ".k-next",
                    btnPrev: ".k-prev",
                    visible: clipsVisible,
                    mouseWheel: true,
                    circular: false,
                    vertical: verical
                });
                // test if k-carousel is too large for scroll buttons:
                if (!verical && $clipListTarget.find('.k-carousel').width() > $clipListTarget.width() - 40) {
                    $clipListTarget.find('.k-carousel').css('width',
                            $clipListTarget.width() - 40
                            )
                }

                // sort ul elements:
                $clipsUl.find('li').sortElements(function (a, b) {
                    return $smh(a).data('index') > $smh(b).data('index') ? 1 : -1;
                });

                ppv_obj.activateEntry(first_clip);
            });
        },
        checkCatInvetory: function (pid, sm_ak, uid, entryId) {
            kdp.sendNotification('doPause');
            var sessData = {
                entryId: entryId,
                pid: pid,
                sm_ak: sm_ak,
                access: paid
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=check_cat_inventory",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                if (!data) {
                    kdp.sendNotification('changeMedia', {
                        'entryId': entryId
                    });
                } else {
                    kdp.setKDPAttribute('servicesProxy.kalturaClient', 'ks', data);
                    kdp.sendNotification('changeMedia', {
                        'entryId': entryId
                    });
                    paid = true;
                }
            });
        },
        normalizeAttrValue: function (attrValue) {
            // normalize flash kdp string values
            switch (attrValue) {
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
        getAttr: function (attr) {
            return this.normalizeAttrValue(
                    kdp.evaluate('{' + attr + '}')
                    );
        },
        getConfig: function (attr) {
            return this.getAttr(this.pluginName + '.' + attr);
        },
        destroyToolTipRegisterLogin: function () {
            $smh('#smh-register-form input').tooltipster('destroy');
            $smh('#smh-login-form input').tooltipster('destroy');
        },
        destroyToolTipLogin: function () {
            $smh('#smh-login-form input').tooltipster('destroy');
        },
        destroyToolTipPass: function () {
            $smh('#smh-password-form input').tooltipster('destroy');
        },
        destroyToolTipActivate: function () {
            $smh('#smh-activation-form input').tooltipster('destroy');
        },
        destroyToolTipSettings: function () {
            $smh('#smh-settings-form input').tooltipster('destroy');
        }
    }
    window['freePreviewEndHandler'] = function () {
        paused = true;
        clearTimeout(fadeout_timer_m);
        clearTimeout(fadeout_timer);
        $smh("#purchaseWindow").stop(true, true).fadeOut();
        $smh('#purchaseWindow').stop(true, false).fadeIn();
        $smh(document).unbind('mousemove');
        $smh(document).unbind('mouseleave');
        $smh(document).unbind('fadeIn');
        $smh(document).unbind('fadeOut');
        $smh(window).unbind('touchmove');
        var pid = ppv_obj.getConfig('pid');
        var entryId = ppv_obj.getConfig('entryId');
        var uiconf_width = ppv_obj.getConfig('width');
        var uiconf_height = ppv_obj.getConfig('height');
        var sm_ak = ppv_obj.getConfig('sm_ak');
        if (ppv_type == 'p') {
            var sessData = {
                pid: pid,
                entry_id: entryId,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_get_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                window.ppv.loadPayWallFP(playerId, pid, entryId, data, uiconf_width, uiconf_height);
            });
        } else if (ppv_type == 'cl' || ppv_type == 'cr' || ppv_type == 'ct' || ppv_type == 'cb') {
            var sessData = {
                pid: pid,
                cat_id: entryId,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/index.php?action=w_get_cat_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                window.ppv.loadPayWallFP(playerId, pid, entryId, data, uiconf_width, uiconf_height);
            });
        } else {
            window.ppv.loadPayWallFP(playerId, pid, entryId, entryId, uiconf_width, uiconf_height);
        }
    }

    window['onDoSeek'] = function () {
        kdp.sendNotification('doPlay');
        setTimeout(function () {
            kdp.sendNotification('doPlay');
        }, 1000);
    };

    window['playerPlayedHandler'] = function () {
        paused = false;
        var entryId = ppv_obj.getConfig('entryId');
        window.ppv.fadeLogout(playerId, entryId);
        if (is_logged_in && !init_loaded) {
            init_loaded = true;
            ppv_obj.updateView();
        }
    }

    window['playerPausedHandler'] = function () {
        paused = true;
        clearTimeout(fadeout_timer_m);
        clearTimeout(fadeout_timer);
        $smh("#purchaseWindow").stop(true, true).fadeOut();
        $smh('#purchaseWindow').stop(true, false).fadeIn();
        $smh(document).unbind('mousemove');
        $smh(document).unbind('mouseleave');
        $smh(document).unbind('fadeIn');
        $smh(document).unbind('fadeOut');
        $smh(window).unbind('touchmove');
        $smh('#purchaseWindow').show();
    }

    window['purchaseHandler'] = function () {
        if (is_logged_in) {
            ppv_obj.showLoggedInPurchaseWindow();
        } else {
            ppv_obj.showPurchaseWindow();
        }
    }

    window['loginHandler'] = function () {
        ppv_obj.login_form_button(login_entryid);
    }
    ppv_obj = new ppv(kdp);

});

$smh(document).on("keypress", "#smh-register-form", function (event) {
    if (event.which == 13 && !event.shiftKey) {
        if ($smh('#register-tab').length > 0) {
            ppv_obj.register(true);
        } else {
            ppv_obj.register(false);
        }
    }
});
$smh(document).on("keypress", ".tab-content #smh-login-form", function (event) {
    if (event.which == 13 && !event.shiftKey) {
        ppv_obj.smhLogin(true);
    }
});
$smh(document).on("keypress", "#form-wrapper #smh-login-form", function (event) {
    if (event.which == 13 && !event.shiftKey) {
        ppv_obj.smhLogin(false);
    }
});
$smh(document).on("keypress", "#smh-password-form", function (event) {
    if (event.which == 13 && !event.shiftKey) {
        ppv_obj.pass_reset_form();
    }
});

window.$smh('body').append('<div class="modal fade" id="smh_purchase_window" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header"></div>' +
        '<div class="modal-body"></div>' +
        '</div>' +
        '</div>' +
        '</div>');

function dump(arr, level) {
    var dumped_text = "";
    if (!level)
        level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for (var j = 0; j < level + 1; j++)
        level_padding += "    ";

    if (typeof (arr) == 'object') { //Array/Hashes/Objects 
        for (var item in arr) {
            var value = arr[item];

            if (typeof (value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value, level + 1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>" + arr + "<===(" + typeof (arr) + ")";
    }
    return dumped_text;
}
