<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-type"></meta>
        <meta content="width=device-width, initial-scale=1" name="viewport"></meta>
        <title>Thank You</title>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
        <style>
            body{
                background-color:#E2EBEC;
            }

            .pf-checkout-container{
                -moz-box-shadow:    0px 1px 4px 0px rgba(50, 50, 50, 0.19);
                -webkit-box-shadow: 0px 1px 4px 0px rgba(50, 50, 50, 0.19);
                background-color:#FFFFFF;
                border-radius: 2px;
                border:1px solid #DEDEDE;
                box-shadow:         0px 1px 4px 0px rgba(50, 50, 50, 0.19);
                font-family: 'Myriad Pro';
                margin-bottom:35px;
                margin-left:auto; 
                margin-right:auto;
                max-width:880px;
                min-width:478px;
                padding:0px;
                position: relative;
            }

            .pf-checkout-container h1{
                color:#414141;
                font-family: 'Myriad Pro';
                font-size: 27px;
                font-weight: 700;
                margin-bottom:15px;
                margin-left:10%;
                margin-top:15px;
            }

            p.pf-processed-msg{
                color: #73737b;
                font-size: 15px;
                font-weight: 400;
                margin-bottom: 50px;
                margin-top: 20px;
                padding-bottom: 0;
                text-align: center;
            }

            .pf-total-col{
                background-color:#F5F5F5;
                border-bottom:1px solid #F0F0F0;
                border-top:1px solid #F0F0F0;
                display: inline-block;
                height:55px;
                margin-bottom: 10px;
                margin-left:0px;
                width:100%;
            }


            .pf-total-wrapper{
                float:right;
                font-weight: 600;
                margin-right:10%;
                margin-top:15px;
            }

            .pf-total-paid{ 
                color: #292929;
                float: left;
                font-size: 21px;
                margin-left: 10%;
                margin-right: 50px;
                margin-top: 17px;
            }

            .pf-total-label{
                color:#737373;
                font-size: 21px;
                font-style: italic;
                margin-right:50px;
            }

            .pf-total-amount{
                color:#ee4e22;
                font-size: 23px;
            }

            .pf-checkout-container .pf-card-details,.pf-checkout-container .pf-success-details{
                display: inline-block;
                margin-left:10%;
                margin-right:10%;
                min-width: 300px;
                width:80%;
            }

            .pf-checkout-container .pf-card-details .pf-input-container{
                display: inline-block;
                float:left;
                margin-top:15px;
                width:50%;
            }

            .pf-checkout-container span.pf-label{
                color:#7A7A7A;
                color:black;
                display: block;
                font-size: 14px;
                height:25px;
                margin-bottom:0px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                width:100%;
            }

            .pf-checkout-container input.pf-input{
                border-radius: 2px;
                border: 1px solid #E3E3E3;
                color: #000;
                display: block;
                font-size: 15px;
                font-weight: 600;
                height: 38px;
                padding: 2%;
                width: 100%;
            }

            .pf-checkout-container input.pf-input:hover{
                border-color:#bbbbbb;
            }

            .pf-checkout-container input.pf-input:focus{
                -moz-box-shadow: 0px 1px 4px 0px rgba(50, 50, 50, 0.19);
                -webkit-box-shadow: 0px 1px 4px 0px rgba(50, 50, 50, 0.19);
                border-color:#bbbbbb;
                box-shadow: 0px 1px 4px 0px rgba(50, 50, 50, 0.19);
            }

            .pf-checkout-container .pf-input-container.pf-error input.pf-input{
                background-color: #f0dede !important;
                border-color: #c60f13 !important;
                color:black;
                border-radius: 2px 2px 0px 0px;
            }

            .pf-checkout-container span.pf-error{
                display: block;
                border-radius: 0px;
                box-sizing: border-box;
                color: white;
                height: 24px;
                padding-left: 10px;
                background-color:#c60f13;
                font-size: 13px;
                line-height: 25px;
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                position: relative;
                top:-10px;
            }

            .pf-checkout-container .pf-error.pf-hide{
                display: none;
            }

            .pf-checkout-container .pf-card-details div.pf-first-name{
                margin-right:5%;
                width:47.5%;
            }

            .pf-checkout-container .pf-card-details div.pf-last-name{
                width:47.5%;
            }

            .pf-checkout-container .pf-card-details div.pf-address{
                margin-right:3%;
                width:76.5%;
            }

            .pf-checkout-container .pf-card-details div.pf-card-expiry{
                width:20.5%;
            }

            .pf-checkout-container .pf-card-details div.pf-card-number{
                margin-right:3%;
                width:76.5%;
            }

            .pf-checkout-container .pf-card-details div.pf-security-code{
                margin-top:20px;
                position: relative;
                width:20.5%;
            }

            .pf-code-icon{
                background-color: #F5F5F5;
                background-repeat: no-repeat;
                left:103%;
                position: absolute;
                top:32px;
                background-image: url(code.png);
                background-size: 38px 24px;
                display: inline-block;
                height: 24px;
                margin-left: 5px;
                width: 38px;
            }

            .pf-checkout-container .pf-card-details div.pf-card-expiry input,
            .pf-checkout-container .pf-card-details div.pf-security-code input{
                padding-left:5%;
                padding-right:5%;
            }

            .pf-card-type{
                float:right;
                text-align: right;
                width:70%;
            }

            .pf-checkout-container .pf-card-number span.pf-label{
                float:left;
                margin-top:5px;
                width:30%;
            }

            .pf-card-type span{
                background-color: #F5F5F5;
                background-size: 38px 24px;
                cursor: pointer;
                display: inline-block;
                height: 24px;
                margin-left: 5px;
                opacity: 0.4;
                width: 38px;
            }

            .pf-visa{
                background-image: url(visa.png);
                background-repeat: no-repeat;
            }

            .pf-mc{
                background-image: url(mc.png);
                background-repeat: no-repeat;
            }

            .pf-amex{
                background-image: url(amex.png);
                background-repeat: no-repeat;
            }

            .pf-discover{
                background-image: url(discover.png);
                background-repeat: no-repeat;
            }

            .pf-card-type .pf-selected{
                opacity: 1;
            }

            .pf-checkout-container .pf-green{
                color:#4FC12D;
            }

            .pf-checkout-container .pf-error{
                color:#dd4b39;
            }

            .pf-checkout-container div.pf-error{
                display: inline-block;
            }

            .pf-success-details p{
                color:#737373;
            }

            .pf-success-details ul{
                margin:0px;
                padding:0px;
                list-style-type: none;
            }

            .pf-success-details ul li{
                height:32px;
                line-height: 32px;
                font-size: 18px;
                color:#737373;
                display: flex;
            }

            .pf-success-details ul li .pf-value-name{
                width:130px;
            }

            .pf-success-details ul li .pf-value{
                color:#292929;
            }

            .pf-companylogo{
                display: block;
                margin-left:auto;
                margin-right:auto;
                margin-top:20px;
                margin-bottom:10px;
                max-width: 400px;
            }

            .pf-payment-buttons{
                margin-bottom:15px;
                margin-top:30px;
                min-width: 300px;
                text-align: right;
                width:100%;
            }

            .pf-payment-buttons button{
                background-color: #ee4e22;
                border-radius: 3px;
                border: medium none;
                color: #FFFFFF;
                font-size: 15px;
                font-weight: 600;
                height: 38px;
                margin-right: 10%;
                text-align: center;
                width: 195px;
            }

            .pf-payment-buttons button:hover{
                background-color:#EE6F22;
            }

            .pf-footer{
                position: absolute;
                text-align: center;
                width:100%;
            }

            .pf-footer span{
                color:#414141;
                display: inline-block;
                font-size: 15px;
                margin-right:10px;
                position: relative;
                top:-8px;
            }

            .pf-footer img{
                border:none;
                display: inline-block;
                height:23px;
                margin-top:10px;
            }

            .pf-loading{
                width: 100%;
                height: 500px;
                display: inline-block;
                background-image: url(loading-small.gif);
                background-position: center center;
                background-repeat: no-repeat;
            }

            .pf-checkout-container .pf-loading.pf-hide{
                display: none;
            }

            .pf-checkout-container .pf-checkout.pf-hide{
                display: none;
            }
        </style>
        <script>
            function Redirect(url) {
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
            }
        </script>
    </head>
    <body>
        <div class="pf-checkout-container pf-success-page">
            <?php
            require_once("pptransact.php");
            $transact = new pptransact();
            $data = explode("|", $_GET["data"]);

            $sm_ak = $data[3];
            $uid = $data[1];
            $smh_aff = $data[14];
            $payerId = $_GET["PayerID"];
            $token = $_GET["token"];
            $amt = $data[0];
            $entryId = $data[2];
            $orderId = $data[4];
            $subId = $data[5];
            $ticketId = $data[6];
            $ticket_type = $data[7];
            $bill_per = $data[8];
            $recurr_desc = $data[9];
            $mobile = $data[11];
            $tz = $data[13];
            $type = $data[15];
            $url = $data[10];

            $transact->commitPayment($sm_ak, $uid, $smh_aff, $payerId, $token, $amt, $entryId, $orderId, $subId, $ticketId, $ticket_type, $bill_per, $recurr_desc, $mobile, $type, $tz, $url);
            ?>            
        </div>
    </body>
</html>