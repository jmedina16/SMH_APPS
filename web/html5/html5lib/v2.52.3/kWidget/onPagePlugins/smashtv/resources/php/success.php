<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Thank you</title>
        <?php
        require_once("pptransact.php");
        $transact = new pptransact();
        $data = explode("|", $_GET["data"]);
        $returnObj = $transact->commitPayment($_GET["PayerID"], $_GET["token"], $data[0], $data[1], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8]);
        ?>

        <script>
            function parentExists() {
                return (parent.location == window.location)? false : true;
            }
		
            function closeFlow(param) {
                    var action = <?= $returnObj ?>;                    
                    parent.postMessage(JSON.stringify(action), "*");
            }
		
            function forceCloseFlow() {
                //The page you want to redirect the user after successfully storing data in local storage.
                //window.location.href = '../../index.html';
			
                // This case is for iPhone - we're closing the purchase window to go back to the main gallery
                window.close();
            }
        </script>
    </head>
    <body onload="closeFlow(false)">
    </body>
</html>