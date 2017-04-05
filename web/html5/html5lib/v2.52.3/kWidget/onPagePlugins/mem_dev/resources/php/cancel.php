<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Purchase Cancelled</title>
        <?php
        require_once("pptransact.php");
        $transact = new pptransact();
        $data = explode("|", $_GET["data"]);
        $transact->cancelOrder($data[0], $data[1]);
        ?>
        <script>
            function closeFlow() {
                var mobile = <?= $data[3] ?>;
                if(mobile){
                    var url = "<?php echo base64_decode($data[2]) ?>";
                    Redirect(url);
                } else {
                    var action = {
                        'action': 'cancel'
                    }                            
                    parent.postMessage(JSON.stringify(action), "*");
                    setTimeout ( forceCloseFlow, '3000' );
                }
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
            
            function forceCloseFlow() {
                //The page you want to redirect the user after successfully storing data in local storage.
                //window.location.href = '../../index.html';
                // This case is for iPhone - we're closing the purchase window to go back to the main gallery
                window.close();
            }
        </script>
    </head>
    <body onload="closeFlow()">
        <div style="background-color:#FFF;height:400px;width:300px; border-radius:8px;padding:20px;">
            Purchase Cancelled
            <button id="close" onclick="closeFlow();">close</button>
        </div>
    </body>
</html>