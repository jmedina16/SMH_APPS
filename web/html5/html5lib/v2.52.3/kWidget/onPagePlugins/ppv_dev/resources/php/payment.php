<!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-type">
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <title>Payment Terminal</title>
        <link rel="stylesheet" type="text/css" href="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/css/font-awesome.min.css" />
        <link rel="stylesheet" type="text/css" href="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/css/payment.css" />
        <script type="text/javascript" src="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/resources/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/payment.js"></script>
        <script type="text/javascript" src="https://mediaplatform.streamingmediahosting.com/html5/html5lib/v2.35/kWidget/onPagePlugins/ppv_dev/resources/js/jquery.payment.min.js"></script>
    </head>
    <body>
        <div id="payment-wrapper">
            <div class="row row-centered">
                <div class="col-md-4 col-md-offset-4">
                    <div id="checkout">
                        <h1 id="payment-header">Review Order</h1>
                        <form novalidate autocomplete="on" id="payment-form" class="form-horizontal">
                            <fieldset>
                                <div class="col-sm-5 detail-m"><img width="100%" src="https://mediaplatform.streamingmediahosting.com/p/10012/thumbnail/entry_id/0_pdnjb70x/width/300" /></div>
                                <div class="col-sm-6 detail-m">
                                    <div class="form-group">
                                        <label for="PaymentAmount">Title</label>
                                        <div class="amount-placeholder">
                                            <span>sample_iPod</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="PaymentAmount">Payment amount</label>
                                        <div class="amount-placeholder">
                                            <span>$</span>
                                            <span>500.00</span>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="form-group">
                                    <div class="title-details">
                                        This title will be available to watch a total of <span style="font-weight: bold;">10 times</span>. It will expire in<span style="font-weight: bold;"> 2 days</span> of purchase.
                                    </div>
                                </div>                                   
                            </fieldset>
                            <fieldset>
                                <legend>User Details</legend>
                                <div class="form-group has-feedback">
                                    <label for="textinput" class="col-sm-3">Full Name</label>
                                    <div class="col-sm-6">
                                        Jorge Medina
                                    </div>
                                </div>
                                <div class="form-group has-feedback">
                                    <label for="textinput" class="col-sm-3">Email</label>
                                    <div class="col-sm-6">
                                        jmedina616@gmail.com
                                    </div>
                                </div>                       
                            </fieldset>
                            <fieldset>
                                <legend>Card Details</legend>
                                <div class="col-sm-12">
                                    <div class="card-row">
                                        <span class="visa"></span><span class="mastercard"></span><span class="amex"></span><span class="discover"></span>
                                    </div>
                                    <div class="form-group">
                                        <label>Name on card</label>
                                        <input type="text" name="cardName" class="form-control" id="cc-name" placeholder="Name on card">
                                    </div>
                                    <div class="form-group">
                                        <label>Card number</label>
                                        <input id="cc-number" name="cardNumber" type="tel" class="card-image form-control cc-number" autocomplete="cc-number" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" required>
                                    </div>
                                    <div class="expiry-date-group form-group">
                                        <label>Expiry date</label>
                                        <input id="cc-exp" name="cardExpiry" type="tel" placeholder="MM / YY" class="form-control cc-exp" autocomplete="cc-exp" required>
                                    </div>
                                    <div class="security-code-group form-group">
                                        <label>Security code</label>
                                        <div class="input-container">
                                            <input id="cc-cvc" name="cardCVC" type="tel" class="form-control cc-cvc" placeholder="&bull;&bull;&bull;" autocomplete="off" required><i class="fa fa-question-circle"></i>
                                        </div>
                                        <div style="display:none;right:0;top:0;" class="popover top">
                                            <div style="left:auto;right:16px;" class="arrow"></div>
                                            <div class="popover-content">
                                                <p>
                                                    <span>For Visa, Mastercard, and Discover (left), the 3 digits on the </span>
                                                    <em>back</em>
                                                    <span> of your card.</span>
                                                </p>
                                                <p>
                                                    <span>For American Express (right), the 4 digits on the </span>
                                                    <em>front</em>
                                                    <span> of your card.</span>
                                                </p>
                                                <div class="cvc-preview-container two-card">
                                                    <div class="amex-cvc-preview"></div>
                                                    <div class="visa-mc-dis-cvc-preview"></div>                                                    
                                                </div>                                                
                                            </div>                                            
                                        </div>                                        
                                    </div>
                                    <div class="zip-code-group form-group">
                                        <label>ZIP/Postal code</label>
                                        <div class="input-container">
                                            <input id="zipcode" name="zipcode" type="tel" class="form-control" placeholder="Zip code" required><i class="fa fa-question-circle"></i>
                                        </div>
                                        <div style="display: none; left: 11px; top: 372.7px;" class="popover top">
                                            <div style="left:auto;right:16px;" class="arrow"></div>
                                            <div class="popover-content">
                                                <div>Enter the ZIP/Postal code for your credit card's billing address.</div>                                                
                                            </div>                                            
                                        </div>                                        
                                    </div>
                                    <div class="row" style="display:none;">
                                        <div class="col-xs-12">
                                            <p class="payment-errors"></p>
                                        </div>
                                    </div>
                                    <button class="btn btn-block btn-success submit-button" id="PayButton" onclick="smhPay.submitPayment(); return false;">
                                        <span class="submit-button-lock fa fa-lock"></span><span class="align-middle">Pay Now</span>
                                    </button>
                                    <div id="disclaimer">Your card will be charged after clicking on the "Pay Now" button. This is a secure 128-bit SSL encrypted payment.</div>
                                </div>
                            </fieldset>
                        </form>
                    </div>   
                    <div id="cancel" style="margin-top: 20px; font-size: 16px ! important; text-align: center; color: rgb(44, 154, 183);"><a href="#">Cancel and return to site</a></div>
                </div>
            </div>
        </div>
    </body>
</html>