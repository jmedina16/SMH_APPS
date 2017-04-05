kWidget.addReadyCallback(function (playerId) {
    window.kdp = $smh('#' + playerId).get(0);
    var addOnce = false;
    var genClipListId = 'k-clipList-' + playerId;
    // remove any old genClipListId:
    $smh('#' + genClipListId).remove();

    var mem = function (kdp) {
        return this.init(kdp);
    }
    mem.prototype = {
        pluginName: 'mem',
        init: function (kdp) {
            this.kdp = kdp;
            this.pid = this.getConfig('pid');
            if (!blocked) {
                kdp.addJsListener("freePreviewEnd", 'freePreviewEndHandler');
            }

            kdp.addJsListener("playerPlayed", "playerPlayedHandler");
            kdp.addJsListener("playerPaused", "playerPausedHandler");

            if (!livestream && !playlist && !category) {
                kdp.addJsListener("playerUpdatePlayhead", "playerUpdatePlayheadHandler");
            }

            if (media_type == 6) {
                this.loadCat();
            }
        },
        register: function () {
            var timezone = jstz.determine();
            var tz = timezone.name();
            var valid = register_validator.form();
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var currentURL = window.location.href;
            var owner_attr_values = new Array();
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');

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

            if (valid) {
                var fname = $smh('#smh-fname').val();
                var lname = $smh('#smh-lname').val();
                var email = $smh('#smh-email').val();
                var pass = $smh('#smh-register-pass').val();

                var sessData = {
                    pid: pid,
                    sm_ak: sm_ak,
                    fname: fname,
                    lname: lname,
                    email: email,
                    pass: pass,
                    tz: tz,
                    url: mem_obj.base64_encode(currentURL),
                    attrs: JSON.stringify(owner_attr_values),
                    type: type,
                    entryId: entryId
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=register_account",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#register-button').attr('disabled', '');
                        $smh('#register-loading').html('<img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading.gif">');
                    }
                }).done(function (data) {
                    if (data['success']) {
                        if (data['auth_key']) {
                            if (blocked) {
                                window.mem.endResize();
                            }
                            is_logged_in = true;
                            $smh('#login-loading').empty();
                            $smh.cookie('smh_auth_key', data['auth_key']);
                            userId = data['user_id'];
                            window.mem.fadeLogout(playerId, entryId);
                            if (!active_interval_set) {
                                window.mem.isActive(pid, sm_ak, data['user_id']);
                                is_active = setInterval(function () {
                                    window.mem.isActive(pid, sm_ak, data['user_id']);
                                }, 600000);
                                active_interval_set = true;
                            }

                            if (!playlist && !category) {
                                kdp.setKDPAttribute('servicesProxy.kalturaClient', 'ks', data['token']);
                                kdp.sendNotification('cleanMedia');
                                kdp.sendNotification('changeMedia', {
                                    'entryId': entryId
                                });
                            }
                            setTimeout(function () {
                                refresh_player = false;
                                if (!playlist && !category) {
                                    kdp.sendNotification('doPlay');
                                } else if (playlist || category) {
                                    window.mem.loadVideo(data['token'], pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                                }

                                $smh('#smh_mem_window').modal('hide');
                            }, 5000);
                            mem_obj.regConfForm();
                        } else {
                            mem_obj.activateForm(email);
                        }
                    } else {
                        $smh('#register-fail').html('User already exists. Please try again');
                        $smh('#register-fail').css('display', 'block');
                        $smh('#register-loading').empty();
                        setTimeout(function () {
                            $smh('#register-fail').css('display', 'none');
                        }, 3000);
                    }
                });
            }
        },
        activateForm: function (email) {
            var header, content;
            $smh('.modal-dialog').css('width', '400px');

            header = '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Activate</h4>';
            $smh('#smh_mem_window .modal-header').html(header);

            content = '<div id="pass-instructions">Thank you for registering! A confirmation email has been sent to:<br><br> <strong>' + email + '</strong>.<br><br> Please click on the activation link in the email to activate your account. If you did not receive an email, please check your spam.</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-default" style="float: right;" data-dismiss="modal">Close</button>' +
                    '</div>' +
                    '<div class="clear"></div>';
            $smh('#smh_mem_window .modal-body').html(content);
        },
        regConfForm: function () {
            var header, content;
            $smh('.modal-dialog').css('width', '400px');

            header = '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Registration Confirmation</h4>';
            $smh('#smh_mem_window .modal-header').html(header);

            content = '<div id="pass-instructions">Thank you for registering!</div>' +
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-default" style="float: right;" data-dismiss="modal">Close</button>' +
                    '</div>' +
                    '<div class="clear"></div>';
            $smh('#smh_mem_window .modal-body').html(content);
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
        showCurrentTime: function (data, id) {
            current_time = data;
        },
        login_register_form: function () {
            var header, content;
            $smh('#smh_mem_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '398px');

            header = '<button type="button" class="close" onclick="mem_obj.refresh();mem_obj.destroyToolTipRegisterLogin();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Welcome</h4>';
            $smh('#smh_mem_window .modal-header').html(header);

            content = '<div style="margin-left: auto; margin-right: auto; margin-bottom: 16px; color: #777; font-weight: normal; font-size: 14px; width: 286px;">Please login or register to continue watching.</div>' +
                    '<ul role="tablist" class="nav nav-tabs">' +
                    '<li class="active" role="presentation"><a data-toggle="tab" role="tab" aria-controls="home" href="#login-tab" aria-expanded="true" onclick="mem_obj.clearRegister();">Login</a></li>' +
                    '<li role="presentation"><a data-toggle="tab" role="tab" aria-controls="profile" href="#register-tab" aria-expanded="false" onclick="mem_obj.clearLogin();">Register</a></li>' +
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
                    '<button type="button" class="btn btn-primary" style="font-size: 11px; margin-left: 5px;" id="signin" onclick="mem_obj.smhLogin(); return false;">Login</button>' +
                    '<div style="float: right; margin-right: 15px; margin-top: 7px;"><a href="#" onclick="mem_obj.smh_login_password_form(); return false;">Forgot Password?</a></div>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="login-loading"></span>' +
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
                    '<div id="register-buttons">' +
                    '<button type="button" class="btn btn-primary" style="float: right; width: 95px;" onclick="mem_obj.register(); return false;">Register</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right;" id="register-loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</div>';
            $smh('#smh_mem_window .modal-body').html(content);

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
        register_window: function () {
            mem_obj.resetModal();
            var header, content;
            var additional_attr = '';
            $smh('#smh_mem_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '398px');
            header = '<span style="font-size: 15px;">Register</span><button type="button" class="close" onclick="mem_obj.destroyToolTipRegisterLogin();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button>';
            $smh('#smh_mem_window .modal-header').html(header);

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
                    '<button type="button" class="btn btn-primary" id="register-button" style="float: right; width: 95px;" onclick="mem_obj.register(); return false;">Register</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right;" id="register-loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>';

            $smh('#smh_mem_window .modal-body').html(content);

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
        resetModal: function () {
            $smh('#smh_mem_window .modal-content').html('<div class="modal-header"></div><div class="modal-body"></div>');
        },
        login_form: function () {
            mem_obj.resetModal();
            var header, content;
            $smh('#smh_mem_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '398px');

            header = '<span style="font-size: 15px;">Login</span><button type="button" class="close" onclick="mem_obj.destroyToolTipLogin();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button>';
            $smh('#smh_mem_window .modal-header').html(header);

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
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px;" id="signin" onclick="mem_obj.smhLogin(false); return false;">Login</button>' +
                    '<div style="float: right; margin-right: 15px; margin-top: 7px;"><a href="#" onclick="mem_obj.smh_login_password_form(); return false;">Forgot Password?</a></div>' +
                    '<span style="margin-left: 20px; margin-right: 10px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="login-loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>' +
                    '</div>';
            $smh('#smh_mem_window .modal-body').html(content);

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
        smhLogin: function () {
            var pid = this.getConfig('pid');
            var sm_ak = this.getConfig('sm_ak');
            var entryId = this.getConfig('entryId');
            var type = this.getConfig('type');
            var uiconf_id = this.getConfig('uiConfId');
            var uiconf_width = this.getConfig('uiConf_width');
            var uiconf_height = this.getConfig('uiConf_height');
            var valid = login_validator.form();
            if (valid) {
                var username = $smh('#smh-username').val();
                var password = $smh('#smh-pass').val();
                var sessData = {
                    pid: this.getConfig('pid'),
                    sm_ak: sm_ak,
                    un: username,
                    pswd: password,
                    type: type,
                    entryId: entryId
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=login_user",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#login-loading').html('<img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading.gif">');
                    }
                }).done(function (data) {
                    if (data['success']) {
                        if (blocked) {
                            window.mem.endResize();
                        }
                        is_logged_in = true;
                        $smh('#login-loading').empty();
                        $smh.cookie('smh_auth_key', data['auth_key']);
                        userId = data['user_id'];
                        window.mem.fadeLogout(playerId, entryId);

                        if (!active_interval_set) {
                            window.mem.isActive(pid, sm_ak, data['user_id']);
                            is_active = setInterval(function () {
                                window.mem.isActive(pid, sm_ak, data['user_id']);
                            }, 600000);
                            active_interval_set = true;
                        }

                        $smh('.modal-dialog').css('width', '515px');
                        $smh('.modal-content').html('<div id="mem-loading"><img width="200px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading_icon.gif"></div>');

                        if (!playlist && !category) {
                            kdp.setKDPAttribute('servicesProxy.kalturaClient', 'ks', data['token']);
                            kdp.sendNotification('cleanMedia');
                            kdp.sendNotification('changeMedia', {
                                'entryId': entryId
                            });

                            if (!livestream) {
                                kdp.addJsListener('playerSeekEnd', 'onDoSeek');
                                setTimeout(function () {
                                    kdp.sendNotification('doSeek', current_time);
                                }, 2000);
                            }
                        }

                        setTimeout(function () {
                            refresh_player = false;
                            if (!playlist && !category) {
                                kdp.sendNotification('doPlay');
                            } else if (playlist || category) {
                                window.mem.loadVideo(data['token'], pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
                            }

                            $smh('#smh_mem_window').modal('hide');
                        }, 5000);
                    } else if (!data['success']) {
                        if (data['au']) {
                            var content = '<h2>Multiple Logins Detected</h2>' +
                                    '<div id="multi-login">You are logged in from at least one other location.<br /> Please log out of other location(s) to access this video.</div>';
                            $smh('#smh_mem_window .modal-body').html(content);
                        } else {
                            if (data['blocked']) {
                                var content = '<h2>We apologize...</h2>' +
                                        '<div id="multi-login">This video is currently not available. Please try again later.</div>';
                                $smh('#smh_mem_window .modal-body').html(content);
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
            $smh('#smh_mem_window').modal('hide');
            is_logged_in = false;
            clearTimeout(fadeout_timer_m);
            clearTimeout(fadeout_timer);
            $smh("#memWindow").stop(true, true).fadeOut();
            $smh('#memWindow').stop(true, false).fadeIn();
            $smh(document).unbind('mousemove');
            $smh(document).unbind('mouseleave');
            $smh(document).unbind('fadeIn');
            $smh(document).unbind('fadeOut');
            $smh(document).unbind('touchmove');
            window.mem.loadVideo('', pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
            window.mem.isNotActive(pid, sm_ak);
        },
        smh_login_password_form: function () {
            var header, content;
            $smh('.modal-dialog').css('width', '370px');

            header = '<button type="button" class="close" onclick="mem_obj.destroyToolTipPass();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Password Recovery</h4>';
            $smh('#smh_mem_window .modal-header').html(header);

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
                    '<button type="button" class="btn btn-primary" style="float: right; width: 95px;" id="pass-submit" onclick="mem_obj.pass_reset_form(); return false;">Submit</button>' +
                    '<button type="button" class="btn btn-default" style="float: right; margin-right: 10px;" onclick="mem_obj.login_form(); return false;">Back</button>' +
                    '<span style="margin-left: 20px; margin-right: 20px; display: block; height: 20px; width: 20px; float: right; margin-top: 7px;" id="loading"></span>' +
                    '</div>' +
                    '<div class="clear"></div>' +
                    '</div>' +
                    '</form>';

            $smh('#smh_mem_window .modal-body').html(content);

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
                    url: mem_obj.base64_encode(currentURL)
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=reset_request",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#pass-submit').attr('disabled', '');
                        $smh('#loading').html('<img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading.gif">');
                    }
                }).done(function (data) {
                    var header, content;
                    if (data['success']) {
                        $smh('.modal-dialog').css('width', '400px');
                        header = '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">Password Recovery</h4>';
                        $smh('#smh_mem_window .modal-header').html(header);

                        content = '<div id="pass-instructions">An email has been sent to <strong>' + email + '</strong> with instructions on how to reset your password.</div>' +
                                '</div>' +
                                '<div id="register-buttons">' +
                                '<button type="button" class="btn btn-default" data-dismiss="modal" style="float: right;">Close</button>' +
                                '</div>' +
                                '<div class="clear"></div>';
                        $smh('#smh_mem_window .modal-body').html(content);
                    }
                });
            }
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
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=get_user_details",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                $smh('#settings-tab').html('<div class="profile-row">' +
                        '<span class="profile-option">First Name</span><span class="profile-change" onclick="mem_obj.changeFname(\'' + data['fname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['fname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Last Name</span><span class="profile-change" onclick="mem_obj.changeLname(\'' + data['lname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['lname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Email</span><span class="profile-change" onclick="mem_obj.changeEmail(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['email'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Password</span><span class="profile-change" onclick="mem_obj.changePsswd(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">*******</div>');
            });
        },
        changeEmail: function (email) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="mem_obj.smh_back_settings(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<div id="smh-request">' +
                    '<h2 style="font-size: 13px;">To change your email, click on the button below to submit your request.</h2>' +
                    '<button onclick="mem_obj.updateEmail(\'' + email + '\'); return false;" id="update" style="float: none; margin-left: auto; margin-right: auto;" class="btn btn-primary" type="button">Submit Request</button>' +
                    '<span id="request-btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading.gif"></span>' +
                    '</div>');
        },
        changePsswd: function (email) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="mem_obj.smh_back_settings(); return false;">Back</button></div>' +
                    '<div class="clear"></div>' +
                    '<div id="smh-request">' +
                    '<h2 style="font-size: 13px;">To change your password, click on the button below to submit your request.</h2>' +
                    '<button onclick="mem_obj.updatePassword(\'' + email + '\'); return false;" id="update" style="float: none; margin-left: auto; margin-right: auto;" class="btn btn-primary" type="button">Submit Request</button>' +
                    '<span id="request-btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading.gif"></span>' +
                    '</div>');
        },
        updateEmail: function (email) {
            var sm_ak = this.getConfig('sm_ak');
            var currentURL = window.location.href;
            var sessData = {
                pid: this.getConfig('pid'),
                sm_ak: sm_ak,
                email: email,
                url: mem_obj.base64_encode(currentURL)
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=reset_email_request",
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
                url: mem_obj.base64_encode(currentURL)
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=reset_psswd_request",
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
        smhProfile: function () {
            var header, content;
            $smh('#smh_mem_window').modal({
                backdrop: 'static'
            });
            $smh('.modal-dialog').css('width', '600px');

            mem_obj.resetModal();
            header = '<button type="button" class="close" onclick="mem_obj.destroyToolTipSettings();" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-remove"></i></span><span class="sr-only">Close</span></button><h4 class="modal-title" id="myModalLabel">User Profile</h4>';
            $smh('#smh_mem_window .modal-header').html(header);

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
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=get_user_details",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                content = '<div id="settings-tab" class="tab-pane text-center active" role="tabpanel">' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">First Name</span><span class="profile-change" onclick="mem_obj.changeFname(\'' + data['fname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['fname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Last Name</span><span class="profile-change" onclick="mem_obj.changeLname(\'' + data['lname'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['lname'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Email</span><span class="profile-change" onclick="mem_obj.changeEmail(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">' + data['email'] + '</div>' +
                        '<div class="profile-row">' +
                        '<span class="profile-option">Password</span><span class="profile-change" onclick="mem_obj.changePsswd(\'' + data['email'] + '\');">Change</span>' +
                        '<div class="clear"></div>' +
                        '</div>' +
                        '<div class="profile-value">*******</div>' +
                        '</div>';
                $smh('#smh_mem_window .modal-body').html(content);
            });
        },
        changeFname: function (fname) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="mem_obj.smh_back_settings(); return false;">Back</button></div>' +
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
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px; float: right;" id="update" onclick="mem_obj.updateFname(); return false;">Update</button>' +
                    '<div id="result"></div>' +
                    '<span id="btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading.gif"></span>' +
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
                var type = this.getConfig('type');
                var entryId = this.getConfig('entryId');
                var fname = $smh('#smh-settings-form #smh-fname').val();
                var sessData = {
                    fname: fname,
                    pid: pid,
                    sm_ak: sm_ak,
                    auth_key: smh_sess,
                    type: type,
                    entryId: entryId
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=update_fname",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#btn-loading').css('display', 'block');
                        $smh('#update').attr('disabled', '');
                    }
                }).done(function (data) {
                    $smh('#btn-loading').css('display', 'none');
                    if (data['success']) {
                        $smh('#smh_mem_window #result').html('<span class="label label-success">Successfully Updated!</span>');
                        setTimeout(function () {
                            $smh('#smh_mem_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    } else {
                        $smh('#smh_mem_window #result').html('<span class="label label-danger">Error, could not update!</span>');
                        setTimeout(function () {
                            $smh('#smh_mem_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    }
                });
            }
        },
        changeLname: function (lname) {
            $smh('#settings-tab').html('<div style="float: left;"><button type="button" class="btn btn-default" onclick="mem_obj.smh_back_settings(); return false;">Back</button></div>' +
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
                    '<button type="button" class="btn btn-primary" style="margin-left: 5px; float: right;" id="update" onclick="mem_obj.updateLname(); return false;">Update</button>' +
                    '<div id="result"></div>' +
                    '<span id="btn-loading"><img width="20px" src="' + protocol + '://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.52.3/kWidget/onPagePlugins/mem/resources/img/loading.gif"></span>' +
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
                var type = this.getConfig('type');
                var entryId = this.getConfig('entryId');
                var lname = $smh('#smh-settings-form #smh-lname').val();
                var sessData = {
                    lname: lname,
                    pid: pid,
                    sm_ak: sm_ak,
                    auth_key: smh_sess,
                    type: type,
                    entryId: entryId
                }
                $smh.ajax({
                    type: "GET",
                    url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=update_lname",
                    data: sessData,
                    dataType: 'json',
                    beforeSend: function () {
                        $smh('#btn-loading').css('display', 'block');
                        $smh('#update').attr('disabled', '');
                    }
                }).done(function (data) {
                    $smh('#btn-loading').css('display', 'none');
                    if (data['success']) {
                        $smh('#smh_mem_window #result').html('<span class="label label-success">Successfully Updated!</span>');
                        setTimeout(function () {
                            $smh('#smh_mem_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    } else {
                        $smh('#smh_mem_window #result').html('<span class="label label-danger">Error, could not update!</span>');
                        setTimeout(function () {
                            $smh('#smh_mem_window #result').empty();
                            $smh('#update').removeAttr('disabled');
                        }, 3000);
                    }
                });
            }
        },
        getClipListTarget: function () {
            // check for generated id:
            if ($smh('#' + genClipListId).length) {
                return  $smh('#' + genClipListId);
            }
            var clipListId = mem_obj.getConfig('clipListTargetId');
            if (clipListId == "null") {
                clipListId = null;
            }
            // check for clip target:
            if (clipListId && $smh('#' + clipListId).length) {
                return  $smh('#' + clipListId)
            }
            // Generate a new clip target ( if none was found )           
            var layout = mem_obj.getConfig('layoutMode');

            if (layout == 'top') {
                return $smh('<div />').attr('id', genClipListId).insertBefore($smh('#' + playerId));
            } else {
                return $smh('<div />').attr('id', genClipListId).insertAfter($smh('#' + playerId));
            }
        },
        activateEntry: function (activeEntryId) {
            var $carousel = mem_obj.getClipListTarget().find('.k-carousel');
            // highlight the active clip ( make sure only one clip is highlighted )
            var $clipList = mem_obj.getClipListTarget().find('ul li');
            ;
            if ($clipList.length && activeEntryId) {
                $clipList.each(function (inx, clipLi) {
                    // kdp moves entryId to .entryId in playlist data provider ( not a db mapping )
                    var entryMeta = $smh(clipLi).data('entryMeta');
                    var clipEntryId = entryMeta;
                    if (clipEntryId == activeEntryId) {
                        $smh(clipLi).addClass('k-active').data('activeEntry', true);

                        // scroll to the target entry ( if not already shown ):
                        if (inx == 0 || mem_obj.getClipListTarget().find('ul').width() > mem_obj.getClipListTarget().width()) {
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
                mem_obj.activateEntry(clip['entryId']);
            });

            kdp.kBind("mediaReady", function () {
                var pid = mem_obj.getConfig('pid');
                var sm_ak = mem_obj.getConfig('sm_ak');

                if (addOnce) {
                    return;
                }
                var clipListId = mem_obj.getConfig('clipListTargetId');
                if (clipListId == "null") {
                    clipListId = null;
                }
                addOnce = true;

                // check for a target
                $clipListTarget = mem_obj.getClipListTarget();
                // Add a base style class:
                $clipListTarget.addClass('kWidget-clip-list');

                // add layout mode:
                var layoutMode = mem_obj.getConfig('layoutMode') || 'right';
                $clipListTarget.addClass('k-' + layoutMode);

                // get the thumbWidth:
                var thumbWidth = mem_obj.getConfig('thumbWidth') || '110';
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
                                mem_obj.setupCat(pid, sm_ak, clip['entry_id']);
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

                mem_obj.activateEntry(first_clip);
            });
        },
        setupCat: function (pid, sm_ak, entryId) {
            kdp.sendNotification('doPause');
            var sessData = {
                entryId: entryId,
                pid: pid,
                sm_ak: sm_ak,
                is_logged_in: is_logged_in
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=activate_cat_entry",
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
        destroyToolTipPass: function () {
            $smh('#smh-password-form input').tooltipster('destroy');
        },
        destroyToolTipLogin: function () {
            $smh('#smh-login-form input').tooltipster('destroy');
        },
        destroyToolTipSettings: function () {
            $smh('#smh-settings-form input').tooltipster('destroy');
        },
        refresh: function () {
            var pid = mem_obj.getConfig('pid');
            var entryId = mem_obj.getConfig('entryId');
            var type = mem_obj.getConfig('type');
            var sm_ak = mem_obj.getConfig('sm_ak');
            var uiconf_id = mem_obj.getConfig('uiConfId');
            var uiconf_width = mem_obj.getConfig('uiConf_width');
            var uiconf_height = mem_obj.getConfig('uiConf_height');
            if (refresh_player) {
                window.mem.checkAccess(pid, sm_ak, uiconf_id, uiconf_width, uiconf_height, entryId, type);
            }
        }
    }
    window['freePreviewEndHandler'] = function () {
        paused = true;
        clearTimeout(fadeout_timer_m);
        clearTimeout(fadeout_timer);
        $smh("#memWindow").stop(true, true).fadeOut();
        $smh('#memWindow').stop(true, false).fadeIn();
        $smh(document).unbind('mousemove');
        $smh(document).unbind('mouseleave');
        $smh(document).unbind('fadeIn');
        $smh(document).unbind('fadeOut');
        $smh(window).unbind('touchmove');
        var pid = mem_obj.getConfig('pid');
        var entryId = mem_obj.getConfig('entryId');
        var uiconf_width = mem_obj.getConfig('width');
        var uiconf_height = mem_obj.getConfig('height');
        var sm_ak = mem_obj.getConfig('sm_ak');
        if (mem_type == 'p') {
            var sessData = {
                pid: pid,
                entry_id: entryId,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=w_get_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                window.mem.loadWall(playerId, pid, entryId, data, uiconf_width, uiconf_height);
            });
        } else if (mem_type == 'cl' || mem_type == 'cr' || mem_type == 'ct' || mem_type == 'cb') {
            var sessData = {
                pid: pid,
                cat_id: entryId,
                sm_ak: sm_ak
            }
            $smh.ajax({
                type: "GET",
                url: protocol + "://mediaplatform.streamingmediahosting.com/apps/mem/v1.0/index.php?action=w_get_cat_thumb",
                data: sessData,
                dataType: 'json'
            }).done(function (data) {
                window.mem.loadWall(playerId, pid, entryId, data, uiconf_width, uiconf_height);
            });
        } else {
            window.mem.loadWall(playerId, pid, entryId, entryId, uiconf_width, uiconf_height);
        }
    }

    window['playerUpdatePlayheadHandler'] = function (data, id) {
        mem_obj.showCurrentTime(data, id);
    }

    window['onDoSeek'] = function () {
        kdp.sendNotification('doPlay');
        setTimeout(function () {
            kdp.sendNotification('doPlay');
        }, 1000);
    };

    window['playerPlayedHandler'] = function () {
        if (is_logged_in) {
            paused = false;
            var entryId = mem_obj.getConfig('entryId');
            window.mem.fadeLogout(playerId, entryId);
        }
    }

    window['playerPausedHandler'] = function () {
        if (is_logged_in) {
            paused = true;
            clearTimeout(fadeout_timer_m);
            clearTimeout(fadeout_timer);
            $smh("#memWindow").stop(true, true).fadeOut();
            $smh('#memWindow').stop(true, false).fadeIn();
            $smh(document).unbind('mousemove');
            $smh(document).unbind('mouseleave');
            $smh(document).unbind('fadeIn');
            $smh(document).unbind('fadeOut');
            $smh(window).unbind('touchmove');
            $smh('#memWindow').show();
        }
    }

    window['loginHandler'] = function () {
        mem_obj.login_form(login_entryid);
    }

    mem_obj = new mem(kdp);

});

$smh(document).on("keypress", "#smh-register-form", function (event) {
    if (event.which == 13 && !event.shiftKey) {
        mem_obj.register();
    }
});
$smh(document).on("keypress", "#smh-login-form", function (event) {
    if (event.which == 13 && !event.shiftKey) {
        mem_obj.smhLogin();
    }
});

$smh(document).on("keypress", "#smh-password-form", function (event) {
    if (event.which == 13 && !event.shiftKey) {
        mem_obj.pass_reset_form();
    }
});

window.$smh('body').append('<div class="modal fade" id="smh_mem_window" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
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
