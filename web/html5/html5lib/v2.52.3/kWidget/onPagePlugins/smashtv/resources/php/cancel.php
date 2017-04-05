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
                var action = {
                    'action': 'cancel'
                }
                    
                parent.postMessage(JSON.stringify(action), "*");
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