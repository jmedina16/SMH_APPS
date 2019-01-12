<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Thank you</title>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
        <?php
        require_once("pptransact.php");
        $transact = new pptransact();
        $data = explode("|", $_GET["data"]);
        $returnObj = $transact->commitPayment($_GET["PayerID"], $_GET["token"], $data[0], $data[1], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[10]);
        ?>

        <script>
            var t;
            var check = 1;
            function parentExists() {
                return (parent.location == window.location)? false : true;
            }
		
            function closeFlow(param) {
                var mobile = <?= $data[10] ?>;
                if(mobile){
                    $('#loading').html('<img width="500px" src="http://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.66/kWidget/onPagePlugins/ppv_dev/resources/img/loading_icon.gif"><div style="font-size: 38px; text-align: center;">Please wait while we complete your transaction..</div>');
                    checkInventory();
                    t=setInterval(checkInventory,5000);
                } else {
                    var action = <?= $returnObj ?>;                    
                    parent.postMessage(JSON.stringify(action), "*");
                }
            }
		
            function forceCloseFlow() {
                //The page you want to redirect the user after successfully storing data in local storage.
                //window.location.href = '../../index.html';
			
                // This case is for iPhone - we're closing the purchase window to go back to the main gallery
                window.close();
            }
            
            function checkInventory(){
                var sessData = {
                    entryId: '<?= $data[12] ?>',
                    uid: <?= $data[1] ?>,
                    pid: <?= $data[13] ?>,
                    sm_ak: '<?= $data[3] ?>',
                    type: '<?= $data[11] ?>',
                    tz: '<?= $data[14] ?>'
                }
                $.ajax({
                    type: "GET",
                    url: "http://mediaplatform.streamingmediahosting.com/apps/ppv/v1.0/dev.php?action=check_inventory",
                    data: sessData,
                    dataType: 'json'
                }).done(function(data) {
                    if(data){
                        var url = "<?php echo base64_decode($data[9]) ?>";
                        Redirect(url);                 
                    } else {
                        if(check == 10){
                            clearInterval(t);
                            $('#loading').html('<div style="margin-top: 30px; width: 388px; margin-left: auto; margin-right: auto; text-align: center; height: 170px;">'+
                                '<strong>Notice: </strong>Your payment was not completed.<br />'+
                                'You will receive an email once the paypal payment status changes to <strong>completed</strong>.'+
                                '<br /><br />Please contact the site administrator if you have any concerns.<br /><br />'+
                                '</div>'+
                                '<div class="modal-footer">'+
                                '<button style="margin-left: auto; margin-right: auto; width: 112px; display: block; font-size: 15px;" type="button" class="btn btn-primary" data-dismiss="modal" onclick="Redirect(\'<?php echo base64_decode($data[9]) ?>\');">Return to site</button>'+
                                '</div>');
                        } else {
                            check++;
                        }
                        
                    }                                
                });  
            }
            
            function Redirect (url) {
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
                else { window.location.href = url; }
            }
        </script>
    </head>
    <body onload="closeFlow(false)">
        <div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 150px; font-size: 17px;" id="loading"></div>
    </body>
</html>