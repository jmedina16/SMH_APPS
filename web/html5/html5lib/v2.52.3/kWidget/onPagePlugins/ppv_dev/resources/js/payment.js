/*
 *
 *	Streaming Media Hosting
 *	
 *	Dashboard
 *
 *	6-01-2015
 */
//Main constructor
function Payment() {}

//Global variables

//Login prototype/class
Payment.prototype = {
    constructor: Payment,
    //Register payment plugin
    registerPayment:function(){
        $('.cc-number').payment('formatCardNumber'); 
        $('.cc-exp').payment('formatCardExpiry');
        $('.cc-cvc').payment('formatCardCVC');
        $('#zipcode').payment('restrictNumeric');
    },
    //Submit Payment
    submitPayment:function(){
        validator =  $("#payment-form").validate({
            rules: {
                cardName: {
                    required: true
                },
                cardNumber: {
                    required: true,
                    cardNumber: true            
                },
                cardExpiry: {
                    required: true,
                    cardExpiry: true
                },
                cardCVC: {
                    required: true,
                    cardCVC: true
                },
                zipcode:{
                    required: true
                }
            },
            messages: {
                cardName: {
                    required: "Enter the cardholder's name"
                },
                cardNumber: {
                    required: "Enter the card number",
                    cardNumber: "Enter a valid card number"            
                },
                cardExpiry: {
                    required: "Enter the expiry date",
                    cardExpiry: "Enter a valid expiry date"
                },
                cardCVC: {
                    required: "Enter the security code",
                    cardCVC: "Enter a valid security code"
                },
                zipcode:{
                    required: "Enter the zip code"
                }				
            },
            highlight: function(element) {
                $(element).closest('.form-control').removeClass('success').addClass('error');
            },
            unhighlight: function(element) {
                $(element).closest('.form-control').removeClass('error').addClass('success');
            },
            errorPlacement: function(error, element) {
                $(element).closest('.form-group').append(error);
            }
        });
        var valid = validator.form();
        if(valid){
            var sessData = {
                action: "doPayment",
                pid: 10012
            } 
 
            $.ajax({
                cache:      false,
                url:        '/apps/ppv/v1.0/dopayment.php',
                type:       'POST',
                data:       sessData,
                beforeSend: function(){
                    $('#PayButton').attr('disabled','');
                    $('#PayButton').html('Processing <i class="fa fa-spinner fa-pulse"></i>');  
                },
                success:function(data) { 
                    var data = $.parseJSON(data);
                    if(data['success']){
                       
                }  
                },
                error: function(){
                    $('#PayButton').html('Error processing');
                    $('#PayButton').removeClass('btn-success').addClass('btn-danger');
                    $('.payment-errors').text('There was an error processing this payment. Please try again.');
                    $('.payment-errors').closest('.row').show();
                    setTimeout(function(){
                        $('#PayButton').removeAttr('disabled');
                        $('#PayButton').html('<span class="submit-button-lock fa fa-lock"></span><span class="align-middle">Pay Now</span>');
                        $('#PayButton').removeClass('btn-danger').addClass('btn-success');
                        $('.payment-errors').closest('.row').hide();
                    },5000); 
                }
            });
        }
    },
    getURLParameter:function(sParam){
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++){
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam){
                return sParameterName[1];
            }
        }
    },
    cancelOrder: function(url){
        var sessData;
        var sub_id = smhPay.getURLParameter('sid');
        if(sub_id != -1){
            sessData = {
                action: "w_delete_sub",
                sub_id: sub_id,
                sm_ak: smhPay.getURLParameter('sm_ak'),
                pid: smhPay.getURLParameter('pid')
            }
        } else {
            sessData = {
                action: "w_delete_order",
                order_id: smhPay.getURLParameter('oid'),
                sm_ak: smhPay.getURLParameter('sm_ak'),
                pid: smhPay.getURLParameter('pid')
            }
        }
 
        $.ajax({
            cache:      false,
            url:        '/apps/ppv/v1.0/dev.php',
            type:       'GET',
            data:       sessData,
            success:function(data) {   
                var data = $.parseJSON(data);
                if(data['success']){
                    smhPay.redirect(url);       
                } 
            }
        });
    },
    redirect:function(url){
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
    //Register actions
    registerActions:function(){
        $.validator.addMethod("cardNumber", function(value, element) {
            return this.optional(element) || $.payment.validateCardNumber(value);
        }, "Please specify a valid credit card number.");
        $.validator.addMethod("cardExpiry", function(value, element) {    
            /* Parsing month/year uses jQuery.payment library */
            value = $.payment.cardExpiryVal(value);
            return this.optional(element) || $.payment.validateCardExpiry(value.month, value.year);
        }, "Invalid expiration date.");
        $.validator.addMethod("cardCVC", function(value, element) {
            return this.optional(element) || $.payment.validateCardCVC(value);
        }, "Invalid security code.");

        $('.security-code-group').on('mouseover','.fa-question-circle', function(){
            $('.security-code-group .popover').css('display','block');
        }).on('mouseleave','.fa-question-circle', function(){
            $('.security-code-group .popover').css('display','none');
        });
        $('.zip-code-group').on('mouseover','.fa-question-circle', function(){
            $('.zip-code-group .popover').css('display','block');
        }).on('mouseleave','.fa-question-circle', function(){
            $('.zip-code-group .popover').css('display','none');
        });
    }
}

// Main on ready
$(document).ready(function(){
    smhPay = new Payment();
    smhPay.registerActions();
    smhPay.registerPayment();
});
