<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Thank you</title>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
        <script>            
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
    <body>
        <div style="width: 500px; margin-left: auto; margin-right: auto; margin-top: 240px; font-size: 17px;" id="loading">
            <div style="margin-top: 30px; width: 388px; margin-left: auto; margin-right: auto; text-align: center; height: 125px;">
                <h3>Thank you for your purchase</h3>
                Check your email for details on your order. If you don't see an email, please check your spam.
            </div>
            <div class="modal-footer">
                <button style="margin-left: auto; margin-right: auto; width: 112px; display: block; font-size: 15px;" type="button" class="btn btn-primary" data-dismiss="modal" onclick="Redirect('<?php echo base64_decode($_GET['redirect_url']) ?>');">Return to site</button>
            </div>            
        </div>
    </body>
</html>