<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Purchase Cancelled</title>
        <?php
        require_once("pptransact.php");
        $transact = new pptransact();
        $data = explode("|", $_GET["data"]);
        $transact->cancelOrder($data[0], $data[1], $data[2]);
        ?>
        <script>
            function closeFlow() {
                var mobile = <?= $data[4] ?>;
                if (mobile) {
                    var url = "<?php echo base64_decode($data[3]) ?>";
                    Redirect(url);
                } else {
                    window.close();
                }
            }

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
    <body onload="closeFlow()">
        <div style="background-color:#FFF;height:400px;width:300px; border-radius:8px;padding:20px;">
            Order Cancelled
        </div>
    </body>
</html>